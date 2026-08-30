<?php

namespace App\Services\SiteAi\Proposals;

use App\Models\Ai\AiProposal;
use App\Models\Ai\AiProposalItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class AiProposalApprovalService
{
    public function __construct(
        protected AiProposalApplierRegistry $appliers
    ) {
    }


    /**
     * Approve and immediately apply a proposal.
     *
     * Approval is NOT merely a status change.
     *
     * The registered capability applier must successfully
     * save the output into the real destination before the
     * proposal becomes APPLIED.
     */
    public function approveAndApply(
        AiProposal $proposal,
        ?int $approvedBy = null
    ): AiProposal {
        $proposal->loadMissing(
            'items'
        );

        if (!$proposal->isPending()) {
            throw new RuntimeException(
                'Only pending AI proposals can be approved.'
            );
        }

        if ($proposal->items->isEmpty()) {
            throw new RuntimeException(
                'AI proposal contains no items to apply.'
            );
        }

        $applier =
            $this->appliers->get(
                $proposal->capability
            );

        /*
         * Revalidate against the real destination BEFORE
         * changing approval state.
         *
         * The applier is responsible for checking the
         * current tenant/off-server destination and ensuring
         * this proposal still belongs there.
         */
        $applier->validate(
            $proposal
        );

        DB::transaction(
            function () use (
                $proposal,
                $approvedBy
            ): void {
                $locked =
                    AiProposal::query()
                        ->whereKey(
                            $proposal->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $locked->status
                    !== AiProposal::STATUS_PENDING
                ) {
                    throw new RuntimeException(
                        'AI proposal is no longer pending.'
                    );
                }

                $locked->update([
                    'status' =>
                        AiProposal::STATUS_APPROVED,

                    'approved_by' =>
                        $approvedBy,

                    'approved_at' =>
                        now(),

                    'failure_message' =>
                        null,

                    'failed_at' =>
                        null,
                ]);

                AiProposalItem::query()
                    ->where(
                        'proposal_id',
                        $locked->id
                    )
                    ->where(
                        'status',
                        AiProposalItem::STATUS_PENDING
                    )
                    ->update([
                        'status' =>
                            AiProposalItem::STATUS_APPROVED,
                    ]);
            }
        );

        $proposal =
            $proposal->fresh([
                'items',
            ]);

        /*
         * ========================================================
         * REAL WEBSITE APPLICATION
         * ========================================================
         *
         * SaaS:
         * The applier writes into the tenant's own database,
         * storage and/or media system.
         *
         * Off-server:
         * The applier uses the authenticated installation/Core
         * integration so the remote website writes into its own
         * database/storage.
         *
         * Central proposal storage remains audit/control data.
         */
        try {
            $applicationResult =
                $applier->apply(
                    $proposal
                );

            DB::transaction(
                function () use (
                    $proposal,
                    $applicationResult
                ): void {
                    $locked =
                        AiProposal::query()
                            ->whereKey(
                                $proposal->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    if (
                        $locked->status
                        !== AiProposal::STATUS_APPROVED
                    ) {
                        throw new RuntimeException(
                            'AI proposal is not in an approved state.'
                        );
                    }

                    $metadata =
                        is_array(
                            $locked->metadata
                        )
                            ? $locked->metadata
                            : [];

                    $metadata[
                        'application'
                    ] =
                        $applicationResult;

                    $locked->update([
                        'status' =>
                            AiProposal::STATUS_APPLIED,

                        'applied_at' =>
                            now(),

                        'metadata' =>
                            $metadata,

                        'failure_message' =>
                            null,

                        'failed_at' =>
                            null,
                    ]);

                    AiProposalItem::query()
                        ->where(
                            'proposal_id',
                            $locked->id
                        )
                        ->where(
                            'status',
                            AiProposalItem::STATUS_APPROVED
                        )
                        ->update([
                            'status' =>
                                AiProposalItem::STATUS_APPLIED,
                        ]);
                }
            );
        } catch (Throwable $e) {
            /*
             * The proposal was approved by the user, but the
             * website rejected or failed to persist the change.
             *
             * Never claim it was applied.
             */
            AiProposal::query()
                ->whereKey(
                    $proposal->id
                )
                ->update([
                    'status' =>
                        AiProposal::STATUS_FAILED,

                    'failed_at' =>
                        now(),

                    'failure_message' =>
                        mb_substr(
                            $e->getMessage(),
                            0,
                            10000
                        ),
                ]);

            AiProposalItem::query()
                ->where(
                    'proposal_id',
                    $proposal->id
                )
                ->where(
                    'status',
                    AiProposalItem::STATUS_APPROVED
                )
                ->update([
                    'status' =>
                        AiProposalItem::STATUS_FAILED,
                ]);

            throw $e;
        }

        return $proposal->fresh([
            'items',
        ]);
    }


    /**
     * Reject a pending proposal without touching the
     * tenant/off-server website.
     */
    public function reject(
        AiProposal $proposal,
        ?int $userId = null
    ): AiProposal {
        DB::transaction(
            function () use (
                $proposal,
                $userId
            ): void {
                $locked =
                    AiProposal::query()
                        ->whereKey(
                            $proposal->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $locked->status
                    !== AiProposal::STATUS_PENDING
                ) {
                    throw new RuntimeException(
                        'Only pending AI proposals can be rejected.'
                    );
                }

                $metadata =
                    is_array(
                        $locked->metadata
                    )
                        ? $locked->metadata
                        : [];

                if ($userId !== null) {
                    $metadata[
                        'rejected_by'
                    ] =
                        $userId;
                }

                $locked->update([
                    'status' =>
                        AiProposal::STATUS_REJECTED,

                    'rejected_at' =>
                        now(),

                    'metadata' =>
                        $metadata,
                ]);

                AiProposalItem::query()
                    ->where(
                        'proposal_id',
                        $locked->id
                    )
                    ->where(
                        'status',
                        AiProposalItem::STATUS_PENDING
                    )
                    ->update([
                        'status' =>
                            AiProposalItem::STATUS_REJECTED,
                    ]);
            }
        );

        return $proposal->fresh([
            'items',
        ]);
    }
}
