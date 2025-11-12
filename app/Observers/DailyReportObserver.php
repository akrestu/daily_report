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
        $user = User::find($dailyReport->user_id);
        
        // Check if user wants to receive approval notifications
        if ($user && $user->wantsNotification('job_approved')) {
            Notification::create([
                'user_id' => $dailyReport->user_id,
                'daily_report_id' => $dailyReport->id,
                'type' => 'job_approved',
                'message' => "Your job '{$dailyReport->job_name}' has been approved.",
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
                'message' => "Your job '{$dailyReport->job_name}' has been rejected.",
                'is_read' => false,
            ]);
        }
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
        
        $approvers = collect();
        
        // Only notify the assigned PIC (job_pic)
        if ($dailyReport->job_pic) {
            $picUser = User::find($dailyReport->job_pic);
            if ($picUser && $picUser->id !== $dailyReport->user_id) {
                $approvers->push($picUser);
            }
        }
        
        // If PIC is not available or is a staff, also notify leaders in the same department
        $picUser = User::find($dailyReport->job_pic);
        if (!$picUser || $picUser->hasRole('staff')) {
            $departmentLeaders = User::where('role_id', $leaderRoleId)
                ->where('department_id', $dailyReport->department_id)
                ->where('id', '!=', $dailyReport->user_id)
                ->get();
            
            foreach ($departmentLeaders as $leader) {
                if (!$approvers->contains('id', $leader->id)) {
                    $approvers->push($leader);
                }
            }
        }
        
        // Only notify admin if:
        // 1. There's no PIC assigned, OR
        // 2. The PIC is not available (user doesn't exist), OR  
        // 3. It's an escalation scenario (could be added later)
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
                    'message' => "New job report '{$dailyReport->job_name}' needs your approval.",
                    'is_read' => false,
                ]);
            }
        }
    }
}