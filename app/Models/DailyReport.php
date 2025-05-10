<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'job_name',
        'report_date',
        'due_date',
        'description',
        'remark',
        'status',
        'approval_status',
        'job_pic',
        'approved_by',
        'rejection_reason',
        'attachment_path',
        'attachment_original_name',
    ];

    protected $casts = [
        'report_date' => 'date',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_pic');
    }

    /**
     * Get the comments for the daily report.
     */
    public function comments()
    {
        return $this->hasMany(JobComment::class, 'daily_report_id')->orderBy('created_at', 'desc');
    }
}