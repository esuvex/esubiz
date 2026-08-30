<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiProposalItem extends Model
{
    protected $table =
        'central_ai_proposal_items';


    public const STATUS_PENDING =
        'pending';

    public const STATUS_APPROVED =
        'approved';

    public const STATUS_REJECTED =
        'rejected';

    public const STATUS_APPLIED =
        'applied';

    public const STATUS_FAILED =
        'failed';


    protected $fillable = [
        'proposal_id',
        'uuid',
        'item_type',
        'target_key',
        'target_type',
        'target_id',
        'original_value',
        'proposed_value',
        'asset_reference',
        'preview_payload',
        'sort_order',
        'status',
        'metadata',
    ];


    protected $casts = [
        'original_value' =>
            'array',

        'proposed_value' =>
            'array',

        'asset_reference' =>
            'array',

        'preview_payload' =>
            'array',

        'metadata' =>
            'array',
    ];


    protected static function booted(): void
    {
        static::creating(
            function (
                AiProposalItem $item
            ): void {
                if (
                    empty(
                        $item->uuid
                    )
                ) {
                    $item->uuid =
                        (string) Str::uuid();
                }

                if (
                    empty(
                        $item->status
                    )
                ) {
                    $item->status =
                        static::STATUS_PENDING;
                }
            }
        );
    }


    public function proposal(): BelongsTo
    {
        return $this->belongsTo(
            AiProposal::class,
            'proposal_id'
        );
    }
}
