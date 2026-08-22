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
