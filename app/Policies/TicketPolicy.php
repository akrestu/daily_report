<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->getRoleLevel() >= 4;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->getRoleLevel() >= 4) {
            return true;
        }

        return $ticket->assigned_to === $user->id;
    }

    public function assign(User $user): bool
    {
        return $user->isAdmin() || $user->getRoleLevel() >= 4;
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->getRoleLevel() >= 4) {
            return true;
        }

        return $ticket->assigned_to === $user->id;
    }

    public function addComment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}
