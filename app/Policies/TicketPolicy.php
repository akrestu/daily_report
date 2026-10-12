<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Triager: admin, atau anggota departemen layanan (IT) dengan level >= triage_min_level.
     */
    public static function isTriager(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->department?->code === config('tickets.department_code')
            && $user->getRoleLevel() >= config('tickets.triage_min_level');
    }

    public function viewAny(User $user): bool
    {
        return self::isTriager($user);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return self::isTriager($user)
            || $ticket->assigned_to === $user->id
            || $ticket->requester_user_id === $user->id;
    }

    public function assign(User $user): bool
    {
        return self::isTriager($user);
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // Pemohon tidak boleh mengubah status sendiri (konfirmasi selesai ditangani terpisah di fase lifecycle)
        return self::isTriager($user) || $ticket->assigned_to === $user->id;
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
