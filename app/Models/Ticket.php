<?php

namespace App\Models;

use App\Services\TicketSla;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;

class Ticket extends Model
{
    use SoftDeletes;

    const STATUS_OPEN = 'open';

    const STATUS_ASSIGNED = 'assigned';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_PENDING = 'pending';

    const STATUS_RESOLVED = 'resolved';

    const STATUS_CLOSED = 'closed';

    const STATUS_CANCELLED = 'cancelled';

    /** Urutan = urutan tampil di filter & daftar tiket */
    const STATUSES = [
        'open' => ['label' => 'Baru', 'badge' => 'bg-primary'],
        'assigned' => ['label' => 'Ditugaskan', 'badge' => 'bg-info text-dark'],
        'in_progress' => ['label' => 'Dikerjakan', 'badge' => 'bg-warning text-dark'],
        'pending' => ['label' => 'Menunggu Pemohon', 'badge' => 'bg-secondary'],
        'resolved' => ['label' => 'Selesai (menunggu konfirmasi)', 'badge' => 'bg-success'],
        'closed' => ['label' => 'Ditutup', 'badge' => 'bg-dark'],
        'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'bg-light text-muted border'],
    ];

    const IMPACTS = [
        'low' => 'Hanya saya',
        'medium' => 'Satu tim / departemen',
        'high' => 'Banyak orang / operasional terhenti',
    ];

    const URGENCIES = [
        'low' => 'Bisa menunggu (tidak mendesak)',
        'medium' => 'Perlu ditangani hari ini',
        'high' => 'Pekerjaan tidak bisa dilanjutkan',
    ];

    /** Matriks prioritas ITIL: [dampak][urgensi] => prioritas */
    const PRIORITY_MATRIX = [
        'high' => ['high' => 'urgent', 'medium' => 'high', 'low' => 'medium'],
        'medium' => ['high' => 'high', 'medium' => 'medium', 'low' => 'low'],
        'low' => ['high' => 'medium', 'medium' => 'low', 'low' => 'low'],
    ];

    const PRIORITIES = [
        'urgent' => 'Mendesak',
        'high' => 'Tinggi',
        'medium' => 'Sedang',
        'low' => 'Rendah',
    ];

    const TYPES = [
        'incident' => 'Gangguan / Kerusakan',
        'service_request' => 'Permintaan Layanan',
    ];

    protected $hidden = ['tracking_token'];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->tracking_token ??= static::generateTrackingToken();
            app(TicketSla::class)->calculateDueDates($ticket);
        });
    }

    protected $fillable = [
        'ticket_number',
        'employee_id',
        'requester_user_id',
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
        'type',
        'assigned_at',
        'resolution_note',
        'closed_at',
        'close_method',
        'cancelled_at',
        'reopen_count',
        'impact',
        'urgency',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'assigned_at' => 'datetime',
            'closed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'sla_paused_at' => 'datetime',
            'response_breached' => 'boolean',
            'resolution_breached' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    /**
     * Simpan lampiran tiket ke disk privat. Return [path, nama asli].
     */
    public static function storeAttachment(?UploadedFile $file): array
    {
        if (! $file) {
            return [null, null];
        }

        $filename = 'ticket_'.uniqid().'_'.time().'.'.($file->extension() ?: 'bin');

        return [$file->storeAs('attachments', $filename, 'attachments'), $file->getClientOriginalName()];
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

    public function histories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at')->orderBy('id');
    }

    public function publicComments(): HasMany
    {
        return $this->comments()->where('is_internal', false);
    }

    /** Tiket masih berjalan (belum ditutup / dibatalkan) */
    public function isActive(): bool
    {
        return ! in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED], true);
    }

    public static function priorityFor(string $impact, string $urgency): string
    {
        return self::PRIORITY_MATRIX[$impact][$urgency] ?? 'medium';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? '-';
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
        return self::STATUSES[$this->status]['label'] ?? (string) $this->status;
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
        return self::STATUSES[$this->status]['badge'] ?? 'bg-secondary';
    }
}
