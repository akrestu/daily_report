<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStatusHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['ticket_id', 'from_status', 'to_status', 'user_id', 'actor_name', 'note'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDescriptionAttribute(): string
    {
        if ($this->from_status === null) {
            return 'Tiket dibuat';
        }

        if ($this->from_status === $this->to_status) {
            return 'Penanganan dialihkan';
        }

        if ($this->from_status === Ticket::STATUS_RESOLVED && $this->to_status === Ticket::STATUS_IN_PROGRESS) {
            return 'Dibuka kembali oleh pemohon';
        }

        return 'Status: '.(Ticket::STATUSES[$this->to_status]['label'] ?? $this->to_status);
    }
}
