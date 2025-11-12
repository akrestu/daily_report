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

        // Users can view reports within their department
        if ($user->department_id === $dailyReport->department_id) {
            return true;
        }

        // Legacy role support for backward compatibility
        if ($user->isManager() || $user->isDepartmentHead() || $user->isLeader()) {
            return true;
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
        return ($user->id === $dailyReport->user_id && $dailyReport->approval_status === 'pending') || 
                $user->isAdmin();
    }

    public function approve(User $user, DailyReport $dailyReport): bool
    {
        return $user->canApprove($dailyReport->user);
    }
}