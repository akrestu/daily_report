<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Target SLA per prioritas, dalam menit JAM KERJA. Dapat diubah admin (menu Pengaturan SLA).
 */
class SlaPolicy extends Model
{
    protected $fillable = ['priority', 'response_minutes', 'resolution_minutes'];

    protected function casts(): array
    {
        return [
            'response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
        ];
    }

    public static function forPriority(?string $priority): ?self
    {
        return $priority ? static::where('priority', $priority)->first() : null;
    }
}
