<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DailyReport $dailyReport): bool
    {
        // Owner can view their own report
        if ($user->id === $dailyReport->user_id) {
            return true;
        }

        // Admin can view all reports
        if ($user->isAdmin()) {
            return true;
        }

        // PIC can view reports assigned to them
        if ($user->id === $dailyReport->job_pic) {
            return true;
        }

        // Level 5 can view all reports (monitoring role)
        if ($user->isLevel5()) {
            return true;
        }

        // FIXED: More granular department-based access control
        if ($user->department_id === $dailyReport->department_id) {
            // Level 4 (Department Head) can view all reports in their department
            if ($user->isLevel4()) {
                return true;
            }

            // Level 3 can view reports from Level 2 and Level 1 in their department
            if ($user->isLevel3()) {
                $reportOwnerLevel = $dailyReport->user?->getRoleLevel();
                return $reportOwnerLevel && $reportOwnerLevel <= 2;
            }

            // Level 2 can view reports from Level 1 in their department
            if ($user->isLevel2()) {
                $reportOwnerLevel = $dailyReport->user?->getRoleLevel();
                return $reportOwnerLevel && $reportOwnerLevel <= 1;
            }

            // Level 1 can only view:
            // 1. Their own reports (already checked above)
            // 2. Completed/approved reports in their department (for reference)
            if ($user->isLevel1()) {
                return in_array($dailyReport->approval_status, ['approved_by_department_head', 'completed']);
            }
        }

        // Legacy role support for backward compatibility
        if ($user->department_id === $dailyReport->department_id) {
            if ($user->isDepartmentHead()) {
                return true;
            }

            if ($user->isLeader()) {
                // Leaders can view reports they're responsible for
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DailyReport $dailyReport): bool
    {
        return $user->id === $dailyReport->user_id && $dailyReport->approval_status === 'pending';
    }

    public function delete(User $user, DailyReport $dailyReport): bool
    {
        // Owner can delete their own pending reports
        if ($user->id === $dailyReport->user_id && $dailyReport->approval_status === 'pending') {
            return true;
        }

        // Admin can delete any report
        if ($user->isAdmin()) {
            return true;
        }

        // Level 5 can delete reports from their department
        if ($user->isLevel5() && $user->department_id === $dailyReport->department_id) {
            return true;
        }

        return false;
    }

    public function approve(User $user, DailyReport $dailyReport): bool
    {
        return $user->canApprove($dailyReport->user);
    }
}