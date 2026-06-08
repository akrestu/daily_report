<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        $recipients = User::whereHas('role', function ($q) {
            $q->where('slug', 'admin');
        })->orWhereHas('role', function ($q) {
            $q->whereIn('slug', ['level5', 'level6', 'level7', 'level8']);
        })->get();

        foreach ($recipients as $user) {
            if (! $user->wantsNotification('ticket_created')) {
                continue;
            }

            Notification::create([
                'user_id'   => $user->id,
                'type'      => 'ticket_created',
                'message'   => 'Tiket baru masuk: [' . $ticket->ticket_number . '] ' . $ticket->title,
                'is_read'   => false,
            ]);
        }
    }
}
