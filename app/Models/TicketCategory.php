<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'requires_approval'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
