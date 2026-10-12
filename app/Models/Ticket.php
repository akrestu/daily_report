<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;

class Ticket extends Model
{
    use SoftDeletes;

    protected $hidden = ['tracking_token'];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->tracking_token ??= static::generateTrackingToken();
        });
    }

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
        $prefix = 'TKT-'.$date.'-';

        // withTrashed: nomor tiket yang sudah dihapus tetap terpakai (unique constraint)
        $last = static::withTrashed()
            ->where('ticket_number', 'like', $prefix.'%')
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat tiket dengan nomor urut harian. Jika dua request bersamaan mendapat nomor
     * yang sama, unique constraint menolak salah satunya dan nomor dihitung ulang.
     */
    public static function createWithNumber(array $attributes, int $maxAttempts = 5): static
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return static::create($attributes + ['ticket_number' => static::generateTicketNumber()]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Token 8 karakter untuk cek status oleh pemohon (tanpa karakter ambigu 0/O/1/I/L).
     */
    public static function generateTrackingToken(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $token = '';

        for ($i = 0; $i < 8; $i++) {
            $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $token;
    }

    public static function findForTracking(string $ticketNumber, string $token): ?static
    {
        $ticket = static::where('ticket_number', strtoupper(trim($ticketNumber)))->first();

        if (! $ticket || ! $ticket->tracking_token
            || ! hash_equals($ticket->tracking_token, strtoupper(trim($token)))) {
            return null;
        }

        return $ticket;
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
            default => $this->priority,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Terbuka',
            'in_progress' => 'Diproses',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-secondary',
            'medium' => 'bg-info',
            'high' => 'bg-warning text-dark',
            'urgent' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-primary',
            'in_progress' => 'bg-warning text-dark',
            'resolved' => 'bg-success',
            'closed' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}
