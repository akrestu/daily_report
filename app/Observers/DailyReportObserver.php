<?php

namespace App\Observers;

use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;

class DailyReportObserver
{
    /**
     * Handle the DailyReport "updated" event.
     */
    public function updated(DailyReport $dailyReport): void
    {
        // Laporan dari Job Plan tiket disetujui → tiket "Selesai" (termasuk laporan tanpa PIC yang disetujui otomatis)
        if ($dailyReport->wasChanged('approval_status') && $dailyReport->approval_status === 'approved') {
            $this->resolveLinkedTicket($dailyReport);
        }

        // Catatan kerja mandiri (tanpa PIC) disetujui otomatis: tidak perlu notifikasi ke pembuatnya sendiri
        if ($dailyReport->isSelfApproved()) {
            return;
        }

        // Check if approval_status field has been changed
        if ($dailyReport->isDirty('approval_status')) {
            $newStatus = $dailyReport->approval_status;
            $oldStatus = $dailyReport->getOriginal('approval_status');
            
            // If status changed to approved or rejected
            if ($newStatus === 'pending' && $oldStatus === 'rejected') {
                // Laporan ditolak diperbaiki & dikirim ulang → PIC diberi tahu lagi
                $this->notifyApprovers($dailyReport);
            } elseif ($newStatus === 'approved' && $oldStatus !== 'approved') {
                // Create notification for the report owner
                $this->createApprovalNotification($dailyReport);
            } elseif ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
                // Create notification for the report owner
                $this->createRejectionNotification($dailyReport);
            }
        }
    }

    /**
     * Handle the DailyReport "created" event.
     */
    public function created(DailyReport $dailyReport): void
    {
        // Notify approvers when a new job report needs approval (laporan tanpa PIC sudah disetujui otomatis)
        if ($dailyReport->approval_status === 'pending') {
            $this->notifyApprovers($dailyReport);
        } elseif ($dailyReport->approval_status === 'approved') {
            $this->resolveLinkedTicket($dailyReport);
        }
    }

    private function resolveLinkedTicket(DailyReport $dailyReport): void
    {
        if (! $dailyReport->job_plan_id) {
            return;
        }

        $ticket = \App\Models\Ticket::where('job_plan_id', $dailyReport->job_plan_id)->first();
        if ($ticket) {
            app(\App\Services\TicketWorkflow::class)->resolveFromReport($ticket, $dailyReport);
        }
    }
    
    /**
     * Create a notification for approval
     */
    private function createApprovalNotification(DailyReport $dailyReport): void
    {
        $user = User::find($dailyReport->user_id);
        
        // Check if user wants to receive approval notifications
        if ($user && $user->wantsNotification('job_approved')) {
            Notification::create([
                'user_id' => $dailyReport->user_id,
                'daily_report_id' => $dailyReport->id,
                'type' => 'job_approved',
                'message' => "Laporan '{$dailyReport->job_name}' disetujui.",
                'is_read' => false,
            ]);
        }
    }
    
    /**
     * Create a notification for rejection
     */
    private function createRejectionNotification(DailyReport $dailyReport): void
    {
        $user = User::find($dailyReport->user_id);
        
        // Check if user wants to receive rejection notifications
        if ($user && $user->wantsNotification('job_rejected')) {
            Notification::create([
                'user_id' => $dailyReport->user_id,
                'daily_report_id' => $dailyReport->id,
                'type' => 'job_rejected',
                'message' => "Laporan '{$dailyReport->job_name}' ditolak.",
                'is_read' => false,
            ]);
        }
    }
    
    /**
     * Notify users who can approve this report
     */
    private function notifyApprovers(DailyReport $dailyReport): void
    {
        // Get admin role ID for fallback notification
        $adminRoleId = Role::where('slug', 'admin')->pluck('id')->first() ?? 1;

        $approvers = collect();

        // Notify the assigned PIC (job_pic) if set
        if ($dailyReport->job_pic) {
            $picUser = User::find($dailyReport->job_pic);
            if ($picUser && $picUser->id !== $dailyReport->user_id) {
                $approvers->push($picUser);
            }
        }

        // If no PIC assigned, notify admin as fallback
        if (!$dailyReport->job_pic || !User::find($dailyReport->job_pic)) {
            $admins = User::where('role_id', $adminRoleId)
                ->where('id', '!=', $dailyReport->user_id)
                ->get();

            foreach ($admins as $admin) {
                if (!$approvers->contains('id', $admin->id)) {
                    $approvers->push($admin);
                }
            }
        }

        // Create notifications for relevant approvers only (with preference check)
        foreach ($approvers as $approver) {
            // Check if user wants to receive this type of notification
            if ($approver->wantsNotification('pending_approval')) {
                Notification::create([
                    'user_id' => $approver->id,
                    'daily_report_id' => $dailyReport->id,
                    'type' => 'pending_approval',
                    'message' => "Laporan baru '{$dailyReport->job_name}' menunggu persetujuan Anda.",
                    'is_read' => false,
                ]);
            }
        }
    }
}