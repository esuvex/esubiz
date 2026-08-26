<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRoutingRule extends Model
{
    protected $fillable = [
        'route_key',
        'label',
        'ai_model_id',
        'fallback_model_id',
        'is_active',
        'priority',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'settings' => 'array',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(
            AiModel::class,
            'ai_model_id'
        );
    }

    public function fallbackModel(): BelongsTo
    {
        return $this->belongsTo(
            AiModel::class,
            'fallback_model_id'
        );
    }
}
