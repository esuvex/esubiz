<?php

namespace App\Services\SiteAi\Proposals\Appliers;

use App\Models\Ai\AiProposal;
use App\Services\SiteAi\Proposals\AiProposalDestinationRegistry;
use App\Services\SiteAi\Proposals\Contracts\AiProposalApplier;
use RuntimeException;

class ThemeHomepageProposalApplier implements AiProposalApplier
{
    public function __construct(
        protected AiProposalDestinationRegistry $destinations
    ) {
    }


    public function capability(): string
    {
        return 'theme.homepage';
    }


    public function validate(
        AiProposal $proposal
    ): void {
        if (
            $proposal->capability
            !== $this->capability()
        ) {
            throw new RuntimeException(
                'Theme homepage proposal capability mismatch.'
            );
        }

        $proposal->loadMissing(
            'website',
            'items'
        );

        if (!$proposal->website) {
            throw new RuntimeException(
                'Theme homepage proposal requires a website.'
            );
        }

        $destination =
            is_array($proposal->destination)
                ? $proposal->destination
                : [];

        if (
            empty(
                $destination[
                    'setting_prefix'
                ]
            )
        ) {
            throw new RuntimeException(
                'Theme homepage proposal has no settings destination.'
            );
        }

        if ($proposal->items->isEmpty()) {
            throw new RuntimeException(
                'Theme homepage proposal contains no items.'
            );
        }

        $allowedTargets =
            $this->manifestTargets(
                is_array(
                    $proposal->manifest_snapshot
                )
                    ? $proposal->manifest_snapshot
                    : []
            );

        if (empty($allowedTargets)) {
            throw new RuntimeException(
                'Theme homepage proposal manifest exposes no editable targets.'
            );
        }

        foreach (
            $proposal->items
            as $item
        ) {
            $targetKey =
                trim(
                    (string) $item->target_key
                );

            if (
                $targetKey === ''
                || !in_array(
                    $targetKey,
                    $allowedTargets,
                    true
                )
            ) {
                throw new RuntimeException(
                    'Theme homepage proposal contains an out-of-scope target: '
                    . (
                        $targetKey !== ''
                            ? $targetKey
                            : '[missing target]'
                    )
                );
            }
        }

        /*
         * Resolve the actual website persistence transport.
         *
         * SaaS -> tenant DB/storage.
         * Off-server -> remote installation transport later.
         */
        $transport =
            $this->destinations
                ->resolve(
                    $proposal->website,
                    $proposal
                );

        $transport->validate(
            $proposal->website,
            $proposal
        );
    }


    public function apply(
        AiProposal $proposal
    ): array {
        $proposal->loadMissing(
            'website',
            'items'
        );

        $transport =
            $this->destinations
                ->resolve(
                    $proposal->website,
                    $proposal
                );

        /*
         * Revalidate immediately before persistence.
         */
        $transport->validate(
            $proposal->website,
            $proposal
        );

        $values = [];
        $assets = [];

        /*
         * ESUBIZ_STRUCTURED_WIDGET_NESTED_ASSET_APPLY_V1
         *
         * Defer individual card/testimonial images until
         * their parent structured values have been collected.
         */
        $nestedStructuredAssets = [];

        foreach (
            $proposal->items
            as $item
        ) {
            $targetKey =
                trim(
                    (string) $item->target_key
                );

            if ($targetKey === '') {
                throw new RuntimeException(
                    'AI proposal item target is missing.'
                );
            }

            $proposedValue =
                $this->unwrapValue(
                    $item->proposed_value
                );

            /*
             * Nested Feature/Testimonial media must not overwrite
             * the complete parent structured target with one path.
             */
            if (
                $item->target_type
                === 'structured_nested_media'
                && !empty(
                    $item->asset_reference
                )
            ) {
                $nestedStructuredAssets[] =
                    $item;

                continue;
            }


            /*
             * Asset outputs must first enter the website's own
             * media/storage system.
             *
             * The resulting local website path/reference is then
             * saved into the normal theme setting.
             */
            if (
                in_array(
                    $item->item_type,
                    [
                        'image',
                        'media',
                        'file',
                        'video',
                    ],
                    true
                )
                && !empty(
                    $item->asset_reference
                )
            ) {
                $assetResult =
                    $transport->saveAsset(
                        $proposal->website,
                        $proposal,
                        $item->asset_reference,
                        [
                            'target_key' =>
                                $targetKey,

                            'target_type' =>
                                $item->target_type,

                            'target_id' =>
                                $item->target_id,
                        ]
                    );

                $localValue =
                    $assetResult[
                        'value'
                    ]
                    ?? $assetResult[
                        'path'
                    ]
                    ?? $assetResult[
                        'reference'
                    ]
                    ?? null;

                if ($localValue === null) {
                    throw new RuntimeException(
                        'Approved AI asset was stored but returned no website-local reference.'
                    );
                }

                $values[
                    $targetKey
                ] =
                    $localValue;

                $assets[] =
                    $assetResult;

                continue;
            }

            /*
             * Normal text, URL, boolean, number and structured
             * content is written directly into the normal theme
             * setting declared by target_key.
             */
            $values[
                $targetKey
            ] =
                $proposedValue;
        }

        /*
         * Store approved nested photos through the same existing
         * website media pipeline, then merge the returned local
         * path into the correct structured card/testimonial.
         */
        foreach (
            $nestedStructuredAssets
            as $nestedItem
        ) {
            $targetKey =
                trim(
                    (string) $nestedItem->target_key
                );


            if (
                !in_array(
                    $targetKey,
                    [
                        'features_json',
                        'testimonials_json',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Nested AI image has an unsupported parent target.'
                );
            }


            $targetId =
                trim(
                    (string) (
                        $nestedItem->target_id
                        ?? ''
                    )
                );


            if (
                !preg_match(
                    '/^([0-9]+):(image_path|photo_path)$/',
                    $targetId,
                    $match
                )
            ) {
                throw new RuntimeException(
                    'Nested AI image location is invalid.'
                );
            }


            $index =
                (int) $match[1];

            $field =
                $match[2];


            if (
                (
                    $targetKey === 'features_json'
                    && $field !== 'image_path'
                )
                || (
                    $targetKey === 'testimonials_json'
                    && $field !== 'photo_path'
                )
            ) {
                throw new RuntimeException(
                    'Nested AI image field does not match its widget.'
                );
            }


            $collection =
                $values[$targetKey]
                ?? [];


            if (is_string($collection)) {
                $decoded =
                    json_decode(
                        $collection,
                        true
                    );

                if (is_array($decoded)) {
                    $collection =
                        $decoded;
                }
            }


            if (
                !is_array($collection)
                || !isset(
                    $collection[$index]
                )
                || !is_array(
                    $collection[$index]
                )
            ) {
                throw new RuntimeException(
                    'Nested AI image could not find its card/testimonial.'
                );
            }


            $assetResult =
                $transport->saveAsset(
                    $proposal->website,
                    $proposal,
                    $nestedItem->asset_reference,
                    [
                        'target_key' =>
                            $targetKey,

                        'target_type' =>
                            'structured_nested_media',

                        'target_id' =>
                            $targetId,
                    ]
                );


            $localValue =
                $assetResult['value']
                ?? $assetResult['path']
                ?? $assetResult['reference']
                ?? null;


            if ($localValue === null) {
                throw new RuntimeException(
                    'Approved nested AI image returned no website-local reference.'
                );
            }


            $collection[$index][$field] =
                $localValue;

            $collection[$index]['show_image'] =
                true;


            $values[$targetKey] =
                $collection;


            $assets[] =
                $assetResult;
        }


        $saveResult =
            $transport->saveValues(
                $proposal->website,
                $proposal,
                $values
            );

        return [
            'capability' =>
                $this->capability(),

            'website_id' =>
                $proposal->website->id,

            'destination' =>
                $proposal->destination,

            'values' =>
                $saveResult,

            'assets' =>
                $assets,
        ];
    }


    protected function manifestTargets(
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


    protected function unwrapValue(
        mixed $value
    ): mixed {
        /*
         * AiProposalService wraps scalar JSON values as:
         *
         * ['value' => ...]
         *
         * Structured arrays remain unchanged.
         */
        if (
            is_array($value)
            && count($value) === 1
            && array_key_exists(
                'value',
                $value
            )
        ) {
            return $value[
                'value'
            ];
        }

        return $value;
    }
}
