<?php

namespace App\Models\Ai;

use App\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCreditTransaction extends Model
{
    protected $fillable = [
        'website_id',
        'user_id',
        'workspace_id',
        'uuid',
        'request_key',
        'reference',
        'direction',
        'type',
        'credits',
        'balance_before',
        'balance_after',
        'ai_usage_log_id',
        'ai_model_id',
        'route_key',
        'source_type',
        'source_id',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'credits' => 'decimal:6',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
        'metadata' => 'array',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function usageLog(): BelongsTo
    {
        return $this->belongsTo(
            AiUsageLog::class,
            'ai_usage_log_id'
        );
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(
            AiModel::class,
            'ai_model_id'
        );
    }
}
