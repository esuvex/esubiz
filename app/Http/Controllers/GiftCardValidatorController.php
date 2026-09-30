<?php

namespace App\Http\Controllers;

use App\Services\Core\GiftCardService;
use Illuminate\Http\Request;

class GiftCardValidatorController extends Controller
{
    public function index()
    {
        return view('gift-card.validate');
    }

    public function validateCheckout(
        Request $request,
        GiftCardService $giftCards
    ) {
        // CORE_GIFT_CARD_ORDER_PASS_V1
        $coreToken = (string) $request->input('core_checkout_pass', '');
        if ($coreToken !== '') {
            $orderId = (int) $request->input('order_id', 0);
            abort_unless($orderId > 0, 403);

            try {
                app(\App\Services\Marketplace\CoreCheckoutPassService::class)
                    ->resolve($coreToken, $orderId);
            } catch (\RuntimeException $exception) {
                abort(403, 'This checkout link has expired. Open checkout again.');
            }

            $order = \Illuminate\Support\Facades\DB::table('marketplace_orders')
                ->where('id', $orderId)
                ->first();

            abort_unless($order, 404);
            abort_unless(
                \Illuminate\Support\Facades\Auth::onceUsingId((int) $order->buyer_id),
                403
            );

            // Use the saved order price rather than browser-supplied totals.
            $request->merge([
                'amount' => $order->amount,
                'currency' => $order->currency,
            ]);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        try {
            $card = $giftCards->validate(
                $data['code'],
                (float) $data['amount'],
                'checkout'
            );

            if (
                !empty($card->issued_to_user_id)
                && (int) $card->issued_to_user_id !== (int) auth()->id()
            ) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This Gift Card belongs to another user.',
                ], 422);
            }

            if (
                strtoupper($card->currency ?? 'NGN')
                !== strtoupper($data['currency'])
            ) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Gift Card currency does not match this order.',
                ], 422);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Gift Card is valid.',
                'name' => $card->name,
                'currency' => strtoupper($card->currency ?? 'NGN'),
                'balance' => (float) $card->remaining_balance,
                'can_cover_order' =>
                    (float) $card->remaining_balance >= (float) $data['amount'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function validateCard(
        Request $request,
        GiftCardService $giftCards
    ) {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $result = $giftCards->validateCard($data['code']);

        return view('gift-card.validate', [
            'result' => $result,
            'code' => strtoupper(trim($data['code'])),
        ]);
    }
}
