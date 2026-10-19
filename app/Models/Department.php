<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'handles_tickets',
    ];

    protected function casts(): array
    {
        return ['handles_tickets' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Departemen dengan kode bawaan (IT) otomatis menjadi tim layanan tiket bila tidak diatur
        static::creating(function (Department $department) {
            if ($department->handles_tickets === null
                && strtoupper(trim((string) $department->code)) === strtoupper(config('tickets.department_code'))) {
                $department->handles_tickets = true;
            }
        });
    }

    /** Departemen yang anggotanya memproses tiket (diatur admin) */
    public function scopeHandlesTickets($query)
    {
        return $query->where('handles_tickets', true);
    }

    /**
     * Get the users in this department
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the daily reports from this department
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * Get the sections in this department
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
