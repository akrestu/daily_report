<?php

namespace App\Models;

use App\Support\WorkingCalendar;
use Illuminate\Database\Eloquent\Model;

/**
 * Hari libur (tanggal merah / cuti bersama) — tidak dihitung sebagai hari kerja SLA.
 */
class Holiday extends Model
{
    protected $fillable = ['date', 'name'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => WorkingCalendar::flush());
        static::deleted(fn () => WorkingCalendar::flush());
    }
}
