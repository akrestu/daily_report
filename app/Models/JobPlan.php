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

    public function canBeConvertedBy(User $user): bool
    {
        return $this->assignee_id === $user->id && $this->status === 'assigned';
    }
}
