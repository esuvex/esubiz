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
