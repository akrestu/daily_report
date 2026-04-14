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

        $reportOwnerLevel = $dailyReport->user?->getRoleLevel();

        // Level 1-4 cannot view reports from Level 5-7 at all
        $userLevel = $user->getRoleLevel();
        if ($userLevel >= 1 && $userLevel <= 4) {
            if ($reportOwnerLevel >= 5 && $reportOwnerLevel <= 7) {
                return false;
            }
        }

        // Level 8 can view all reports in the same job site (cross-department)
        if ($user->isLevel8()) {
            // Must be in the same job site
            if ($user->job_site_id && $user->job_site_id === $dailyReport->job_site_id) {
                return true;
            }
        }

        // Level 7 can view all reports in same department and lower levels
        if ($user->isLevel7() && $user->department_id === $dailyReport->department_id) {
            return true;
        }

        // Level 6 can view all reports in same department and lower levels
        if ($user->isLevel6() && $user->department_id === $dailyReport->department_id) {
            return true;
        }

        // Level 5 can view all reports in same department (monitoring role)
        if ($user->isLevel5() && $user->department_id === $dailyReport->department_id) {
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
                return $reportOwnerLevel && $reportOwnerLevel <= 2;
            }

            // Level 2 can view reports from Level 1 in their department
            if ($user->isLevel2()) {
                return $reportOwnerLevel && $reportOwnerLevel <= 1;
            }

            // Level 1 can only view:
            // 1. Their own reports (already checked above)
            // 2. Approved reports in their department (for reference)
            if ($user->isLevel1()) {
                return $dailyReport->approval_status === 'approved';
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
        // Level 8 cannot create reports
        if ($user->isLevel8()) {
            return false;
        }

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

        // Level 8 can delete reports from same job site
        if ($user->isLevel8() && $user->job_site_id === $dailyReport->job_site_id) {
            return true;
        }

        // Level 7 can delete reports from their department
        if ($user->isLevel7() && $user->department_id === $dailyReport->department_id) {
            return true;
        }

        // Level 6 can delete reports from their department
        if ($user->isLevel6() && $user->department_id === $dailyReport->department_id) {
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
        // Level 8 can approve cross-department within same job site
        if ($user->isLevel8()) {
            // Must be in the same job site
            if ($user->job_site_id && $user->job_site_id === $dailyReport->job_site_id) {
                // Can approve Level 6 and Level 7 reports
                return $dailyReport->user?->isLevel7() || $dailyReport->user?->isLevel6();
            }
            return false;
        }

        return $dailyReport->user && $user->canApprove($dailyReport->user);
    }
}