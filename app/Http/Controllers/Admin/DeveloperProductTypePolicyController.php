<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\Developer\DeveloperProductTypePolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeveloperProductTypePolicyController extends Controller
{
    public function update(
        Request $request,
        DeveloperProductTypePolicyService $policies
    ) {
        $data = $request->validate([
            'product_type' => [
                'required',
                'string',
                Rule::in(
                    DeveloperProductTypePolicyService::PRODUCT_TYPES
                ),
            ],

            'developer_build_enabled' => [
                'nullable',
                'boolean',
            ],

            'developer_publish_enabled' => [
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

        DB::table('developer_product_type_policies')
            ->updateOrInsert(
                [
                    'product_type' => $productType,
                ],
                [
                    'developer_build_enabled' =>
                        $request->boolean(
                            'developer_build_enabled'
                        ),

                    'developer_publish_enabled' =>
                        $request->boolean(
                            'developer_publish_enabled'
                        ),

                    'saas_max_price' =>
                        $data['saas_max_price']
                        ?? null,

                    'off_server_max_price' =>
                        $data['off_server_max_price']
                        ?? null,

                    'updated_at' => now(),

                    'created_at' =>
                        DB::raw(
                            'COALESCE(created_at, CURRENT_TIMESTAMP)'
                        ),
                ]
            );

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
