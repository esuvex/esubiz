<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'driver',
        'base_url',
        'secret_payload',
        'is_active',
        'is_default',
        'priority',
        'metadata',
    ];

    protected $casts = [
        /*
         * API credentials are encrypted at rest.
         */
        'secret_payload' => 'encrypted:array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(
            AiModel::class
        );
    }
}
