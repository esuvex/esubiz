<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketplaceSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'value_type',
        'group',
        'description',
        'is_active',
    ];

    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeGroup(
        Builder $query,
        string $group
    ): Builder {
        return $query->where('group', $group);
    }
}
