<?php

namespace App\Observers;

use App\Models\JobPlan;
use App\Models\Notification;

class JobPlanObserver
{
    /**
     * Handle the JobPlan "created" event.
     * Notifies the assignee that a new job plan has been assigned to them.
     */
    public function created(JobPlan $jobPlan): void
    {
        $assignee = $jobPlan->assignee;

        if ($assignee && $assignee->wantsNotification('job_plan_assigned')) {
            $creatorName = $jobPlan->creator?->name ?? 'Someone';
            $dueDate     = $jobPlan->due_date?->format('d M Y') ?? '-';

            Notification::create([
                'user_id'     => $assignee->id,
                'job_plan_id' => $jobPlan->id,
                'type'        => 'job_plan_assigned',
                'message'     => "{$creatorName} menugaskan job plan: \"{$jobPlan->job_name}\" (tenggat: {$dueDate}).",
                'is_read'     => false,
            ]);
        }
    }

    /**
     * Handle the JobPlan "updated" event.
     * Notifies the assignee only when content fields change (not status/timestamps).
     * Guard against infinite loop: status update to 'converted' must NOT trigger notification.
     */
    public function updated(JobPlan $jobPlan): void
    {
        // Only notify when content fields change, not when status/converted_at change
        $contentFields = ['job_name', 'description', 'planned_date', 'due_date', 'remark'];

        if (!$jobPlan->isDirty($contentFields)) {
            return;
        }

        // Do not notify if plan has already been converted
        if ($jobPlan->status === 'converted') {
            return;
        }

        $assignee = $jobPlan->assignee;

        if ($assignee && $assignee->wantsNotification('job_plan_updated')) {
            $creatorName = $jobPlan->creator?->name ?? 'Someone';

            Notification::create([
                'user_id'     => $assignee->id,
                'job_plan_id' => $jobPlan->id,
                'type'        => 'job_plan_updated',
                'message'     => "{$creatorName} memperbarui job plan: \"{$jobPlan->job_name}\".",
                'is_read'     => false,
            ]);
        }
    }
}
