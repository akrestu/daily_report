<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master karyawan PT. WBK — dipakai untuk verifikasi NIK pemohon tiket (tidak perlu akun SiGAP).
 */
class Employee extends Model
{
    protected $fillable = [
        'nik',
        'name',
        'department_id',
        'position',
        'job_site_id',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function normalizeNik(?string $nik): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $nik));
    }

    public static function findActiveByNik(?string $nik): ?self
    {
        $nik = static::normalizeNik($nik);

        return $nik === '' ? null : static::active()->with('department')->where('nik', $nik)->first();
    }
}
