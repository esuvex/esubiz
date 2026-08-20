<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Core\CoreAddonMarketplaceFulfilmentService;
use Illuminate\Http\Request;

class CoreAddonFulfilmentController extends Controller
{
    public function fulfil(
        Request $request,
        CoreAddonMarketplaceFulfilmentService $service
    ) {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'product_type' => ['required', 'in:addon,bundle'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'website_id' => ['nullable', 'integer'],
            'workspace_id' => ['nullable', 'integer'],
            'order_id' => ['nullable', 'integer'],
            'order_reference' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $result = $service->fulfil(
            $data['user_id'],
            $data['product_id'],
            $data['product_type'],
            $data['deployment_type'],
            $data['website_id'] ?? null,
            $data['workspace_id'] ?? null,
            $data['order_id'] ?? null,
            $data['order_reference'] ?? null,
            $data['starts_at'] ?? null,
            $data['expires_at'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Core add-on entitlement fulfilled successfully.',
            'data' => $result,
        ]);
    }
}
