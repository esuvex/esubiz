<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Core\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GiftCardController extends Controller
{
    public function index()
    {
        $giftCards = DB::table('gift_cards')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.gift-cards.index', compact('giftCards'));
    }

    public function create()
    {
        return view('admin.gift-cards.create');
    }

    public function store(Request $request, GiftCardService $giftCards)
    {
        $data = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usable_at_checkout' => ['nullable', 'boolean'],
            'usable_for_wallet_funding' => ['nullable', 'boolean'],
            'allow_partial_redemption' => ['nullable', 'boolean'],
            'enabled' => ['nullable', 'boolean'],
        ])->validate();

        $card = $giftCards->issue(
            amount: (float) $data['amount'],
            currency: strtoupper($data['currency']),
            name: $data['name'] ?? null,
            createdBy: auth()->id(),
            settings: [
                'usage_limit' => $data['usage_limit'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'usable_at_checkout' =>
                    $request->boolean('usable_at_checkout'),
                'usable_for_wallet_funding' =>
                    $request->boolean('usable_for_wallet_funding'),
                'allow_partial_redemption' =>
                    $request->boolean('allow_partial_redemption'),
                'enabled' => $request->boolean('enabled', true),
            ]
        );

        return redirect()
            ->route('admin.gift-cards.index')
            ->with(
                'success',
                'Gift card created successfully.'
            );
    }

    public function show(int $id)
    {
        $giftCard = DB::table('gift_cards')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($giftCard, 404);

        $transactions = DB::table('gift_card_transactions')
            ->where('gift_card_id', $id)
            ->orderByDesc('created_at')
            ->paginate(50);

        return view(
            'admin.gift-cards.show',
            compact('giftCard', 'transactions')
        );
    }

    public function update(Request $request, int $id)
    {
        $giftCard = DB::table('gift_cards')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($giftCard, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usable_at_checkout' => ['nullable', 'boolean'],
            'usable_for_wallet_funding' => ['nullable', 'boolean'],
            'allow_partial_redemption' => ['nullable', 'boolean'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        DB::table('gift_cards')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'usage_limit' => $data['usage_limit'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'usable_at_checkout' =>
                    $request->boolean('usable_at_checkout'),
                'usable_for_wallet_funding' =>
                    $request->boolean('usable_for_wallet_funding'),
                'allow_partial_redemption' =>
                    $request->boolean('allow_partial_redemption'),
                'enabled' => $request->boolean('enabled'),
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Gift card settings updated successfully.'
        );
    }

    public function disable(int $id)
    {
        DB::table('gift_cards')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'enabled' => false,
                'status' => 'disabled',
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Gift card disabled.'
        );
    }

    public function enable(int $id)
    {
        DB::table('gift_cards')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'enabled' => true,
                'status' => 'active',
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Gift card enabled.'
        );
    }
}
