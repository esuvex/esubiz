<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    protected $fillable = [
        'ai_provider_id',
        'name',
        'model_key',
        'capabilities',
        'is_active',
        'input_cost_per_million',
        'cached_input_cost_per_million',
        'output_cost_per_million',
        'metadata',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'input_cost_per_million' => 'decimal:6',
        'cached_input_cost_per_million' => 'decimal:6',
        'output_cost_per_million' => 'decimal:6',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(
            AiProvider::class,
            'ai_provider_id'
        );
    }
}
