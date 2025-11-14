<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'is_active',
    ];

    /**
     * Get the casts array.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the daily reports for this job site
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * Scope a query to only include active job sites.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
