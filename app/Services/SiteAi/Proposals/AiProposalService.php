<?php

namespace App\Services\SiteAi\Proposals;

use App\Models\Ai\AiProposal;
use App\Models\Ai\AiProposalItem;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class AiProposalService
{
    /**
     * Create one globally scoped AI proposal.
     *
     * AI output is NOT applied here.
     *
     * The proposal records:
     *
     * - who/what owns the operation
     * - capability
     * - action
     * - authoritative manifest
     * - authoritative invocation context
     * - real destination
     * - proposed output items
     *
     * Approval/application happens separately.
     */
    public function create(
        ?Website $website,
        string $contextType,
        ?string $contextId,
        string $capability,
        string $action,
        array $manifest,
        array $context,
        array $destination,
        array $items,
        array $options = []
    ): AiProposal {
        $contextType =
            $this->requiredIdentifier(
                $contextType,
                'context_type',
                64
            );

        $capability =
            $this->requiredIdentifier(
                $capability,
                'capability',
                150
            );

        $action =
            $this->requiredIdentifier(
                $action,
                'action',
                100
            );

        $contextId =
            $this->optionalText(
                $contextId,
                191
            );

        if (empty($destination)) {
            throw new InvalidArgumentException(
                'AI proposal destination is required.'
            );
        }

        if (empty($items)) {
            throw new InvalidArgumentException(
                'AI proposal must contain at least one item.'
            );
        }

        /*
         * ============================================================
         * ESUBIZ AI SCOPE BOUNDARY
         * ============================================================
         *
         * The capability manifest supplied at generation time is the
         * authoritative boundary for this proposal.
         *
         * It is snapshotted so a proposal cannot later silently gain
         * access to another page, section, module or feature.
         */
        $allowedTargets =
            $this->allowedTargets(
                $manifest
            );

        $normalizedItems = [];

        foreach (
            array_values($items)
            as $index => $item
        ) {
            if (!is_array($item)) {
                throw new InvalidArgumentException(
                    'Every AI proposal item must be an array.'
                );
            }

            $normalizedItems[] =
                $this->normalizeItem(
                    $item,
                    $allowedTargets,
                    $index
                );
        }

        return DB::transaction(
            function () use (
                $website,
                $contextType,
                $contextId,
                $capability,
                $action,
                $manifest,
                $context,
                $destination,
                $normalizedItems,
                $options
            ): AiProposal {
                $proposal =
                    AiProposal::query()
                        ->create([
                            'website_id' =>
                                $website?->id,

                            'installation_id' =>
                                $options[
                                    'installation_id'
                                ] ?? null,

                            'user_id' =>
                                $options[
                                    'user_id'
                                ] ?? null,

                            'workspace_id' =>
                                $options[
                                    'workspace_id'
                                ] ?? null,

                            'context_type' =>
                                $contextType,

                            'context_id' =>
                                $contextId,

                            'capability' =>
                                $capability,

                            'action' =>
                                $action,

                            'proposal_type' =>
                                $this->optionalText(
                                    $options[
                                        'proposal_type'
                                    ] ?? 'change',
                                    64
                                ) ?: 'change',

                            'title' =>
                                $this->optionalText(
                                    $options[
                                        'title'
                                    ] ?? null,
                                    191
                                ),

                            'summary' =>
                                $this->optionalText(
                                    $options[
                                        'summary'
                                    ] ?? null,
                                    10000
                                ),

                            'status' =>
                                AiProposal::STATUS_PENDING,

                            'manifest_snapshot' =>
                                $manifest,

                            'context_snapshot' =>
                                $context,

                            'destination' =>
                                $destination,

                            'preview_payload' =>
                                (array) (
                                    $options[
                                        'preview_payload'
                                    ] ?? []
                                ),

                            'metadata' =>
                                (array) (
                                    $options[
                                        'metadata'
                                    ] ?? []
                                ),
                        ]);

                foreach (
                    $normalizedItems
                    as $item
                ) {
                    $proposal
                        ->items()
                        ->create(
                            $item
                        );
                }

                return $proposal
                    ->fresh([
                        'items',
                    ]);
            }
        );
    }


    /**
     * Return every target exposed by a capability manifest.
     *
     * Supports generic manifests containing:
     *
     * functions[*].targets
     *
     * and future top-level:
     *
     * targets
     */
    public function allowedTargets(
        array $manifest
    ): array {
        $targets = [];

        foreach (
            (array) (
                $manifest['targets']
                ?? []
            )
            as $target
        ) {
            $target =
                trim(
                    (string) $target
                );

            if ($target !== '') {
                $targets[] =
                    $target;
            }
        }

        foreach (
            (array) (
                $manifest['functions']
                ?? []
            )
            as $function
        ) {
            if (!is_array($function)) {
                continue;
            }

            foreach (
                (array) (
                    $function['targets']
                    ?? []
                )
                as $target
            ) {
                $target =
                    trim(
                        (string) $target
                    );

                if ($target !== '') {
                    $targets[] =
                        $target;
                }
            }
        }

        return array_values(
            array_unique(
                $targets
            )
        );
    }


    protected function normalizeItem(
        array $item,
        array $allowedTargets,
        int $index
    ): array {
        $itemType =
            $this->requiredIdentifier(
                (string) (
                    $item['item_type']
                    ?? ''
                ),
                'item_type',
                64
            );

        $targetKey =
            $this->optionalText(
                $item[
                    'target_key'
                ] ?? null,
                191
            );

        /*
         * If a capability exposes explicit targets,
         * proposal items MUST stay inside them.
         *
         * Example:
         *
         * About AI cannot propose hero_title.
         * Theme AI cannot propose social-media content.
         * Product AI cannot modify unrelated website settings.
         */
        if (
            !empty($allowedTargets)
            && (
                $targetKey === null
                || !in_array(
                    $targetKey,
                    $allowedTargets,
                    true
                )
            )
        ) {
            throw new RuntimeException(
                'AI proposal target is outside the active capability scope: '
                . (
                    $targetKey
                    ?? '[missing target]'
                )
            );
        }

        return [
            'item_type' =>
                $itemType,

            'target_key' =>
                $targetKey,

            'target_type' =>
                $this->optionalText(
                    $item[
                        'target_type'
                    ] ?? null,
                    100
                ),

            'target_id' =>
                $this->optionalText(
                    $item[
                        'target_id'
                    ] ?? null,
                    191
                ),

            'original_value' =>
                $this->jsonValue(
                    $item[
                        'original_value'
                    ] ?? null
                ),

            'proposed_value' =>
                $this->jsonValue(
                    $item[
                        'proposed_value'
                    ] ?? null
                ),

            'asset_reference' =>
                $this->jsonArray(
                    $item[
                        'asset_reference'
                    ] ?? []
                ),

            'preview_payload' =>
                $this->jsonArray(
                    $item[
                        'preview_payload'
                    ] ?? []
                ),

            'sort_order' =>
                max(
                    0,
                    (int) (
                        $item[
                            'sort_order'
                        ] ?? $index
                    )
                ),

            'status' =>
                AiProposalItem::STATUS_PENDING,

            'metadata' =>
                $this->jsonArray(
                    $item[
                        'metadata'
                    ] ?? []
                ),
        ];
    }


    protected function requiredIdentifier(
        string $value,
        string $field,
        int $maxLength
    ): string {
        $value =
            trim(
                $value
            );

        if ($value === '') {
            throw new InvalidArgumentException(
                $field . ' is required.'
            );
        }

        if (
            mb_strlen($value)
            > $maxLength
        ) {
            throw new InvalidArgumentException(
                $field . ' is too long.'
            );
        }

        if (
            !preg_match(
                '/^[A-Za-z0-9._:-]+$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                $field
                . ' contains invalid characters.'
            );
        }

        return $value;
    }


    protected function optionalText(
        mixed $value,
        int $maxLength
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        if ($value === '') {
            return null;
        }

        return mb_substr(
            $value,
            0,
            $maxLength
        );
    }


    protected function jsonArray(
        mixed $value
    ): array {
        return is_array($value)
            ? $value
            : [];
    }


    protected function jsonValue(
        mixed $value
    ): array {
        /*
         * JSON model casts expect a JSON-compatible structure.
         *
         * Scalars are wrapped so text, URLs, numbers and booleans
         * retain their exact value without requiring separate
         * database columns per output type.
         */
        if (is_array($value)) {
            return $value;
        }

        return [
            'value' =>
                $value,
        ];
    }
}
