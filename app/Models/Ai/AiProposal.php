<?php

namespace App\Models\Ai;

use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiProposal extends Model
{
    protected $table =
        'central_ai_proposals';


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

    public const STATUS_SUPERSEDED =
        'superseded';


    protected $fillable = [
        'uuid',
        'website_id',
        'installation_id',
        'user_id',
        'workspace_id',
        'context_type',
        'context_id',
        'capability',
        'action',
        'proposal_type',
        'title',
        'summary',
        'status',
        'manifest_snapshot',
        'context_snapshot',
        'destination',
        'preview_payload',
        'metadata',
        'approved_by',
        'approved_at',
        'rejected_at',
        'applied_at',
        'failed_at',
        'failure_message',
    ];


    protected $casts = [
        'manifest_snapshot' =>
            'array',

        'context_snapshot' =>
            'array',

        'destination' =>
            'array',

        'preview_payload' =>
            'array',

        'metadata' =>
            'array',

        'approved_at' =>
            'datetime',

        'rejected_at' =>
            'datetime',

        'applied_at' =>
            'datetime',

        'failed_at' =>
            'datetime',
    ];


    protected static function booted(): void
    {
        static::creating(
            function (
                AiProposal $proposal
            ): void {
                if (
                    empty(
                        $proposal->uuid
                    )
                ) {
                    $proposal->uuid =
                        (string) Str::uuid();
                }

                if (
                    empty(
                        $proposal->status
                    )
                ) {
                    $proposal->status =
                        static::STATUS_PENDING;
                }
            }
        );
    }


    public function website(): BelongsTo
    {
        return $this->belongsTo(
            Website::class,
            'website_id'
        );
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }


    public function items(): HasMany
    {
        return $this->hasMany(
            AiProposalItem::class,
            'proposal_id'
        )->orderBy(
            'sort_order'
        )->orderBy(
            'id'
        );
    }


    public function isPending(): bool
    {
        return $this->status
            === static::STATUS_PENDING;
    }


    public function isApproved(): bool
    {
        return $this->status
            === static::STATUS_APPROVED;
    }


    public function isApplied(): bool
    {
        return $this->status
            === static::STATUS_APPLIED;
    }


    public function canApprove(): bool
    {
        return $this->isPending();
    }


    public function canApply(): bool
    {
        return $this->isApproved();
    }
}
