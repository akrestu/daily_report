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
        // Check if approval_status field has been changed
        if ($dailyReport->isDirty('approval_status')) {
            $newStatus = $dailyReport->approval_status;
            $oldStatus = $dailyReport->getOriginal('approval_status');
            
            // If status changed to approved or rejected
            if ($newStatus === 'approved' && $oldStatus !== 'approved') {
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
        // Notify approvers when a new job report needs approval
        $this->notifyApprovers($dailyReport);
    }
    
    /**
     * Create a notification for approval
     */
    private function createApprovalNotification(DailyReport $dailyReport): void
    {
        Notification::create([
            'user_id' => $dailyReport->user_id,
            'daily_report_id' => $dailyReport->id,
            'type' => 'job_approved',
            'message' => "Your job '{$dailyReport->job_name}' has been approved.",
            'is_read' => false,
        ]);
    }
    
    /**
     * Create a notification for rejection
     */
    private function createRejectionNotification(DailyReport $dailyReport): void
    {
        Notification::create([
            'user_id' => $dailyReport->user_id,
            'daily_report_id' => $dailyReport->id,
            'type' => 'job_rejected',
            'message' => "Your job '{$dailyReport->job_name}' has been rejected.",
            'is_read' => false,
        ]);
    }
    
    /**
     * Notify users who can approve this report
     */
    private function notifyApprovers(DailyReport $dailyReport): void
    {
        // Get role IDs for permission checking
        $adminRoleId = Role::where('slug', 'admin')->pluck('id')->first() ?? 1;
        $departmentHeadRoleId = Role::where('slug', 'department_head')->pluck('id')->first() ?? 2;
        $leaderRoleId = Role::where('slug', 'leader')->pluck('id')->first() ?? 3;
        
        // Get approvers with different criteria based on role
        $approvers = User::where(function ($query) use ($dailyReport, $adminRoleId, $departmentHeadRoleId, $leaderRoleId) {
            // Admins can approve any report
            $query->where('role_id', $adminRoleId);
            
            // Department heads only get notifications if they are the PIC
            $query->orWhere(function ($q) use ($dailyReport, $departmentHeadRoleId) {
                $q->where('role_id', $departmentHeadRoleId)
                  ->where('id', $dailyReport->job_pic);
            });
            
            // Leaders in the same department get notifications
            $query->orWhere(function ($q) use ($dailyReport, $leaderRoleId) {
                $q->where('role_id', $leaderRoleId)
                  ->where('department_id', $dailyReport->department_id);
            });
        })->get();
        
        foreach ($approvers as $approver) {
            // Skip if the approver is the report creator
            if ($approver->id === $dailyReport->user_id) {
                continue;
            }
            
            // Create notification for the approver
            Notification::create([
                'user_id' => $approver->id,
                'daily_report_id' => $dailyReport->id,
                'type' => 'pending_approval',
                'message' => "New job report '{$dailyReport->job_name}' needs your approval.",
                'is_read' => false,
            ]);
        }
    }
}