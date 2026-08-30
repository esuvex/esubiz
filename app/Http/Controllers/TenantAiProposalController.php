<?php

namespace App\Http\Controllers;

use App\Models\Ai\AiProposal;
use App\Models\Website;
use App\Services\SiteAi\Proposals\AiProposalApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TenantAiProposalController extends Controller
{
    /**
     * Return one proposal for the universal preview interface.
     */
    public function show(
        Request $request,
        string $subdomain,
        string $uuid
    ): JsonResponse {
        [$website, $proposal] =
            $this->resolveProposal(
                $subdomain,
                $uuid
            );

        return response()->json([
            'ok' => true,

            'proposal' =>
                $this->proposalPayload(
                    $proposal
                ),
        ]);
    }


    /**
     * Approve the proposal and immediately apply it through
     * its registered capability applier.
     *
     * The controller never writes website content itself.
     */
    public function approve(
        Request $request,
        string $subdomain,
        string $uuid,
        AiProposalApprovalService $approval
    ): JsonResponse {
        [$website, $proposal] =
            $this->resolveProposal(
                $subdomain,
                $uuid
            );

        $proposal =
            $approval->approveAndApply(
                $proposal,
                auth()->id()
            );

        return response()->json([
            'ok' => true,

            'message' =>
                'AI proposal approved and applied successfully.',

            'proposal' =>
                $this->proposalPayload(
                    $proposal
                ),
        ]);
    }


    /**
     * Reject/discard a pending proposal.
     *
     * Nothing is written to the website.
     */
    public function reject(
        Request $request,
        string $subdomain,
        string $uuid,
        AiProposalApprovalService $approval
    ): JsonResponse {
        [$website, $proposal] =
            $this->resolveProposal(
                $subdomain,
                $uuid
            );

        $proposal =
            $approval->reject(
                $proposal,
                auth()->id()
            );

        return response()->json([
            'ok' => true,

            'message' =>
                'AI proposal discarded.',

            'proposal' =>
                $this->proposalPayload(
                    $proposal
                ),
        ]);
    }


    /**
     * Resolve proposal strictly inside the website from
     * which it originated.
     *
     * A proposal UUID from Website A cannot be approved from
     * Website B.
     */
    protected function resolveProposal(
        string $subdomain,
        string $uuid
    ): array {
        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();

        $proposal =
            AiProposal::query()
                ->where(
                    'uuid',
                    $uuid
                )
                ->where(
                    'website_id',
                    $website->id
                )
                ->with([
                    'items',
                ])
                ->firstOrFail();

        return [
            $website,
            $proposal,
        ];
    }


    /**
     * Universal proposal representation.
     *
     * The frontend does not need to understand how the
     * capability persists its output.
     */
    protected function proposalPayload(
        AiProposal $proposal
    ): array {
        $proposal->loadMissing(
            'items'
        );

        return [
            'uuid' =>
                $proposal->uuid,

            'status' =>
                $proposal->status,

            'capability' =>
                $proposal->capability,

            'action' =>
                $proposal->action,

            'proposal_type' =>
                $proposal->proposal_type,

            'title' =>
                $proposal->title,

            'summary' =>
                $proposal->summary,

            'destination' =>
                $proposal->destination,

            'preview_payload' =>
                $proposal->preview_payload,

            'approved_at' =>
                $proposal->approved_at
                    ?->toISOString(),

            'applied_at' =>
                $proposal->applied_at
                    ?->toISOString(),

            'failed_at' =>
                $proposal->failed_at
                    ?->toISOString(),

            'failure_message' =>
                $proposal->failure_message,

            'can_approve' =>
                $proposal->isPending(),

            'can_reject' =>
                $proposal->isPending(),

            'items' =>
                $proposal->items
                    ->map(
                        fn ($item) => [
                            'uuid' =>
                                $item->uuid,

                            'status' =>
                                $item->status,

                            'item_type' =>
                                $item->item_type,

                            'target_key' =>
                                $item->target_key,

                            'target_type' =>
                                $item->target_type,

                            'target_id' =>
                                $item->target_id,

                            'original_value' =>
                                $item->original_value,

                            'proposed_value' =>
                                $item->proposed_value,

                            'asset_reference' =>
                                $item->asset_reference,

                            'preview_payload' =>
                                $item->preview_payload,
                        ]
                    )
                    ->values()
                    ->all(),
        ];
    }
}
