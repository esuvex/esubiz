<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\CreditVolumePricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class CreditVolumeQuoteController extends Controller
{
    public function history(
        Request $request,
        \App\Services\Marketplace\CreditTransactionHistoryService $history
    ): JsonResponse {
        $data = $request->validate([
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $resolved = app(
                \App\Services\Licensing\OffServerApiCredentialService::class
            )->resolveBearerToken((string) $request->bearerToken());

            return response()->json($history->page(
                (int) $resolved['website']->id,
                $data['credit_type'],
                (int) ($data['page'] ?? 1)
            ));
        } catch (\RuntimeException $exception) {
            return response()->json([
                'message' => 'This website is not connected to Esubiz.',
            ], 401);
        }
    }

    public function tiers(
        Request $request,
        \App\Services\Marketplace\CreditVolumeTierCatalogService $catalog
    ): JsonResponse {
        $data = $request->validate([
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($catalog->page(
            $data['credit_type'],
            $data['deployment_type'],
            (int) ($data['page'] ?? 1)
        ));
    }

    public function __invoke(
        Request $request,
        CreditVolumePricingService $pricing
    ): JsonResponse {
        $data = $request->validate([
            'credit_type' => [
                'required',
                'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits',
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        try {
            $quote = $pricing->quote(
                $data['credit_type'],
                (int) $data['quantity'],
                $data['deployment_type'],
                strtoupper($data['currency'] ?? '')
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'credit_type' => $quote['credit_type'],
            'quantity' => $quote['quantity'],
            'deployment_type' => $quote['deployment_type'],
            'currency' => $quote['currency'],
            'unit_price' => $quote['unit_price'],
            'total' => $quote['total'],
            'unit_price_minor' => $quote['unit_price_minor'],
            'total_minor' => $quote['total_minor'],
            'tier_id' => $quote['tier_id'],
        ]);
    }
}
