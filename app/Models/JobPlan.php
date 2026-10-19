<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'assignee_id',
        'department_id',
        'job_site_id',
        'section_id',
        'job_name',
        'description',
        'remark',
        'planned_date',
        'due_date',
        'status',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'planned_date'  => 'date',
            'due_date'      => 'date',
            'converted_at'  => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Daily reports created by converting this plan.
     */
    public function convertedReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'job_plan_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /** Ditutup tanpa laporan (tiket asalnya selesai/dibatalkan lewat jalur lain) */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /** Masih perlu dikerjakan (belum dikonversi ke laporan & tidak ditutup) */
    public function isActive(): bool
    {
        return $this->status === 'assigned';
    }

    public function isLate(): bool
    {
        return $this->isActive() && $this->due_date && $this->due_date->isBefore(today());
    }

    /** Label & tone badge status (satu sumber untuk daftar & detail) */
    public function badge(): array
    {
        return match (true) {
            $this->isConverted() => ['label' => 'Dikonversi', 'tone' => 'approved'],
            $this->isClosed() => ['label' => 'Ditutup', 'tone' => 'closed'],
            $this->isLate() => ['label' => 'Lewat due', 'tone' => 'rejected'],
            default => ['label' => 'Aktif', 'tone' => 'progress'],
        };
    }

    public function canBeConvertedBy(User $user): bool
    {
        return $this->assignee_id === $user->id && $this->status === 'assigned';
    }
}
