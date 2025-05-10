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
        return $user->id === $dailyReport->user_id || 
               $user->isAdmin() || 
               $user->isManager() ||
               $user->isDepartmentHead() ||
               $user->isLeader() ||
               ($user->isStaff() && $user->department_id === $dailyReport->department_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DailyReport $dailyReport): bool
    {
        return $user->id === $dailyReport->user_id && $dailyReport->status === 'pending';
    }

    public function delete(User $user, DailyReport $dailyReport): bool
    {
        return ($user->id === $dailyReport->user_id && $dailyReport->status === 'pending') || 
                $user->isAdmin();
    }

    public function approve(User $user, DailyReport $dailyReport): bool
    {
        return $user->canApprove($dailyReport->user);
    }
}