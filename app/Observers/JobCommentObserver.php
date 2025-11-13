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

        // Collect all user IDs we need to query at once
        $userIdsToLoad = [];

        // Add report owner if different from commenter
        if ($comment->user_id != $dailyReport->user_id) {
            $userIdsToLoad[] = $dailyReport->user_id;
        }

        // Add job PIC if different from owner and commenter
        if ($dailyReport->job_pic &&
            $dailyReport->job_pic != $dailyReport->user_id &&
            $dailyReport->job_pic != $comment->user_id) {
            $userIdsToLoad[] = $dailyReport->job_pic;
        }

        // Get previous commenters
        $previousCommenterIds = JobComment::where('daily_report_id', $dailyReport->id)
            ->where('user_id', '!=', $comment->user_id)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Filter out users already included (owner, PIC)
        $additionalCommenters = array_diff($previousCommenterIds, [
            $dailyReport->user_id,
            $dailyReport->job_pic
        ]);

        $userIdsToLoad = array_merge($userIdsToLoad, $additionalCommenters);

        // Single query to load all users at once - FIXES N+1!
        $users = User::whereIn('id', array_unique($userIdsToLoad))->get()->keyBy('id');

        // Notify report owner if the comment is not from them
        if ($comment->user_id != $dailyReport->user_id) {
            $reportOwner = $users->get($dailyReport->user_id);

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
        if ($dailyReport->job_pic &&
            $dailyReport->job_pic != $dailyReport->user_id &&
            $dailyReport->job_pic != $comment->user_id) {
            $picUser = $users->get($dailyReport->job_pic);

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
        foreach ($additionalCommenters as $commenterId) {
            $commenterUser = $users->get($commenterId);

            if (!$commenterUser) {
                continue;
            }

            // Only notify admin if they are the PIC, otherwise skip admin notifications for comments
            if ($commenterUser->isAdmin() && $commenterId != $dailyReport->job_pic) {
                continue; // Skip admin notification unless they are the PIC
            }

            // Check if user wants to receive comment notifications
            if ($commenterUser->wantsNotification('new_comment')) {
                Notification::create([
                    'user_id' => $commenterId,
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