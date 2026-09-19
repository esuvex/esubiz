<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\Developer\DeveloperProductTypePolicyService;
use App\Services\Marketplace\Settings\MarketplaceProductRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeveloperProductTypePolicyController extends Controller
{
    public function update(
        Request $request,
        DeveloperProductTypePolicyService $policies
    ) {
        if ($request->has('policies')) {
            $registry = app(MarketplaceProductRegistry::class);

            $data = $request->validate([
                'policies' => ['required', 'array'],

                'policies.*.product_type' => [
                    'required',
                    'string',
                    Rule::in($registry->types()),
                ],

                'policies.*.admin_build_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'policies.*.developer_build_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'policies.*.developer_publish_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'policies.*.developer_resell_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'policies.*.saas_max_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'policies.*.off_server_max_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data['policies'] as $policy) {
                    $productType = $policy['product_type'];

                    $values = [
                        'updated_at' => now(),
                    ];

                    foreach ([
                        'admin_build_enabled',
                        'developer_build_enabled',
                        'developer_publish_enabled',
                        'developer_resell_enabled',
                    ] as $field) {
                        if (array_key_exists($field, $policy)) {
                            $values[$field] = (bool) $policy[$field];
                        }
                    }

                    foreach ([
                        'saas_max_price',
                        'off_server_max_price',
                    ] as $field) {
                        if (array_key_exists($field, $policy)) {
                            $values[$field] = $policy[$field];
                        }
                    }

                    $existing = DB::table('developer_product_type_policies')
                        ->where('product_type', $productType)
                        ->exists();

                    if ($existing) {
                        DB::table('developer_product_type_policies')
                            ->where('product_type', $productType)
                            ->update($values);
                    } else {
                        DB::table('developer_product_type_policies')
                            ->insert(array_merge(
                                [
                                    'product_type' => $productType,
                                    'admin_build_enabled' => false,
                                    'developer_build_enabled' => false,
                                    'developer_publish_enabled' => false,
                                    'developer_resell_enabled' => false,
                                    'saas_max_price' => null,
                                    'off_server_max_price' => null,
                                    'created_at' => now(),
                                ],
                                $values
                            ));
                    }
                }
            });

            return back()->with(
                'success',
                'Developer product controls updated.'
            );
        }
        $data = $request->validate([
            'product_type' => [
                'required',
                'string',
                Rule::in(
                    app(MarketplaceProductRegistry::class)->types()
                ),
            ],

            'admin_build_enabled' => [
                'nullable',
                'boolean',
            ],

            'developer_build_enabled' => [
                'nullable',
                'boolean',
            ],

            'developer_publish_enabled' => [
                'nullable',
                'boolean',
            ],

            'developer_resell_enabled' => [
                'nullable',
                'boolean',
            ],

            'saas_max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'off_server_max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $productType = $data['product_type'];

        $values = [
            'updated_at' => now(),
        ];

        /*
         * Each Marketplace Settings form owns only the controls it submits.
         * This prevents Build/Publish saves from disabling Resell, or vice versa.
         */
        foreach ([
            'admin_build_enabled',
            'developer_build_enabled',
            'developer_publish_enabled',
            'developer_resell_enabled',
        ] as $field) {
            if ($request->has($field)) {
                $values[$field] = $request->boolean($field);
            }
        }

        foreach ([
            'saas_max_price',
            'off_server_max_price',
        ] as $field) {
            if ($request->exists($field)) {
                $values[$field] = $data[$field] ?? null;
            }
        }

        $existing = DB::table('developer_product_type_policies')
            ->where('product_type', $productType)
            ->exists();

        if ($existing) {
            DB::table('developer_product_type_policies')
                ->where('product_type', $productType)
                ->update($values);
        } else {
            DB::table('developer_product_type_policies')
                ->insert(array_merge(
                    [
                        'product_type' => $productType,
                        'admin_build_enabled' => false,
                        'developer_build_enabled' => false,
                        'developer_publish_enabled' => false,
                        'developer_resell_enabled' => false,
                        'saas_max_price' => null,
                        'off_server_max_price' => null,
                        'created_at' => now(),
                    ],
                    $values
                ));
        }

        return back()->with(
            'success',
            ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $productType
                )
            )
            . ' developer policy updated.'
        );
    }
}
