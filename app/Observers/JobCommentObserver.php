<?php

namespace App\Observers;

use App\Models\JobComment;
use App\Models\Notification;
use App\Models\User;

class JobCommentObserver
{
    /**
     * Handle the JobComment "created" event.
     */
    public function created(JobComment $comment): void
    {
        $dailyReport = $comment->dailyReport;
        
        // Notify report owner if the comment is not from them
        if ($comment->user_id != $dailyReport->user_id) {
            $reportOwner = User::find($dailyReport->user_id);
            
            // Check if user wants to receive comment notifications
            if ($reportOwner && $reportOwner->wantsNotification('new_comment')) {
                Notification::create([
                    'user_id' => $dailyReport->user_id,
                    'daily_report_id' => $dailyReport->id,
                    'comment_id' => $comment->id,
                    'type' => 'new_comment',
                    'message' => "New comment on your job '{$dailyReport->job_name}'.",
                    'is_read' => false,
                ]);
            }
        }
        
        // Notify the job PIC if different from owner and commenter
        if ($dailyReport->job_pic && $dailyReport->job_pic != $dailyReport->user_id && $dailyReport->job_pic != $comment->user_id) {
            $picUser = User::find($dailyReport->job_pic);
            
            // Check if PIC wants to receive comment notifications
            if ($picUser && $picUser->wantsNotification('new_comment')) {
                Notification::create([
                    'user_id' => $dailyReport->job_pic,
                    'daily_report_id' => $dailyReport->id,
                    'comment_id' => $comment->id,
                    'type' => 'new_comment',
                    'message' => "New comment on job '{$dailyReport->job_name}' where you're listed as PIC.",
                    'is_read' => false,
                ]);
            }
        }
        
        // Notify other relevant commenters (exclude admin unless they are directly involved)
        $previousCommenters = JobComment::where('daily_report_id', $dailyReport->id)
            ->where('user_id', '!=', $comment->user_id)
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        foreach ($previousCommenters as $commenter) {
            // Skip if commenter is the report owner or the job PIC (already notified)
            if ($commenter == $dailyReport->user_id || ($dailyReport->job_pic && $commenter == $dailyReport->job_pic)) {
                continue;
            }
            
            // Only notify admin if they are the PIC, otherwise skip admin notifications for comments
            $commenterUser = User::find($commenter);
            if ($commenterUser && $commenterUser->isAdmin() && $commenter != $dailyReport->job_pic) {
                continue; // Skip admin notification unless they are the PIC
            }
            
            // Check if user wants to receive comment notifications
            if ($commenterUser && $commenterUser->wantsNotification('new_comment')) {
                Notification::create([
                    'user_id' => $commenter,
                    'daily_report_id' => $dailyReport->id,
                    'comment_id' => $comment->id,
                    'type' => 'new_comment',
                    'message' => "New comment on job '{$dailyReport->job_name}' that you previously commented on.",
                    'is_read' => false,
                ]);
            }
        }
    }
} 