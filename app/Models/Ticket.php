<?php

namespace App\Models;

use App\Models\JobPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'requester_name',
        'requester_employee_id',
        'requester_department',
        'requester_phone',
        'job_site_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'job_plan_id',
        'attachment_path',
        'attachment_original_name',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class, 'job_site_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function jobPlan(): BelongsTo
    {
        return $this->belongsTo(JobPlan::class, 'job_plan_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'asc');
    }

    public static function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TKT-' . $date . '-';

        $last = static::where('ticket_number', 'like', $prefix . '%')
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'    => 'Rendah',
            'medium' => 'Sedang',
            'high'   => 'Tinggi',
            'urgent' => 'Mendesak',
            default  => $this->priority,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'Terbuka',
            'in_progress' => 'Diproses',
            'resolved'    => 'Selesai',
            'closed'      => 'Ditutup',
            default       => $this->status,
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low'    => 'bg-secondary',
            'medium' => 'bg-info',
            'high'   => 'bg-warning text-dark',
            'urgent' => 'bg-danger',
            default  => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'bg-primary',
            'in_progress' => 'bg-warning text-dark',
            'resolved'    => 'bg-success',
            'closed'      => 'bg-secondary',
            default       => 'bg-secondary',
        };
    }
}
