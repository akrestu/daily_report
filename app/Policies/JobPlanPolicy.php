<?php

namespace App\Policies;

use App\Models\JobPlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobPlan $jobPlan): bool
    {
        // Creator and assignee can always view
        if ($user->id === $jobPlan->creator_id || $user->id === $jobPlan->assignee_id) {
            return true;
        }

        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        // Senior users in the same department can view
        $creatorLevel = $jobPlan->creator?->getRoleLevel();
        $userLevel    = $user->getRoleLevel();

        if ($user->department_id === $jobPlan->department_id && $userLevel > $creatorLevel) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->getRoleLevel() >= 3;
    }

    public function update(User $user, JobPlan $jobPlan): bool
    {
        return $user->id === $jobPlan->creator_id && $jobPlan->status === 'assigned';
    }

    public function delete(User $user, JobPlan $jobPlan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $jobPlan->creator_id;
    }

    public function convert(User $user, JobPlan $jobPlan): bool
    {
        return $jobPlan->canBeConvertedBy($user);
    }
}
