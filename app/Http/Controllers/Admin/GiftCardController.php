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
            ->paginate(10);

        $giftCardUsage = DB::table('gift_card_transactions as transactions')
            ->leftJoin('gift_cards as cards', 'cards.id', '=', 'transactions.gift_card_id')
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->whereIn('transactions.type', ['redemption', 'refund', 'adjustment'])
            ->orderByDesc('transactions.created_at')
            ->select([
                'transactions.id',
                'transactions.type',
                'transactions.amount',
                'transactions.balance_after',
                'transactions.currency',
                'transactions.usage_context',
                'transactions.reference_type',
                'transactions.reference_id',
                'transactions.created_at',
                'cards.code as gift_card_code',
                'cards.name as gift_card_name',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->paginate(10, ['*'], 'usage_page');

        return view(
            'admin.payment-gateways.gift-card',
            compact('giftCards', 'giftCardUsage')
        );
    }

    public function create()
    {
        return view('admin.payment-gateways.gift-card-create');
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
            ->route('admin.payment-gateways.gift-card')
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
            ->paginate(10);

        return view(
            'admin.payment-gateways.gift-card-show',
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


    public function csv()
    {
        $cards = DB::table('gift_cards')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($cards) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Code',
                'Name',
                'Currency',
                'Initial Amount',
                'Remaining Balance',
                'Usage Limit',
                'Usage Count',
                'Status',
                'Enabled',
                'Starts At',
                'Expires At',
                'Created At',
            ]);

            foreach ($cards as $card) {
                fputcsv($handle, [
                    $card->code,
                    $card->name,
                    $card->currency,
                    $card->initial_amount,
                    $card->remaining_balance,
                    $card->usage_limit,
                    $card->usage_count,
                    $card->status,
                    $card->enabled ? 'Yes' : 'No',
                    $card->starts_at,
                    $card->expires_at,
                    $card->created_at,
                ]);
            }

            fclose($handle);
        }, 'esubiz-gift-cards.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function pdf()
    {
        $cards = DB::table('gift_cards')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.payment-gateways.gift-card-pdf',
            compact('cards')
        )->download(
            'esubiz-gift-cards-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function generate(Request $request, GiftCardService $giftCards)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
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
        ]);

        $quantity = (int) $data['quantity'];

        for ($i = 0; $i < $quantity; $i++) {
            $giftCards->issue(
                amount: (float) $data['amount'],
                currency: strtoupper($data['currency']),
                name: $data['name'] ?? 'Esubiz Gift Card',
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
        }

        return redirect()
            ->route('admin.payment-gateways.gift-card')
            ->with(
                'success',
                $quantity . ' gift card' . ($quantity === 1 ? '' : 's') . ' generated successfully.'
            );
    }

    public function destroy(int $id)
    {
        $giftCard = DB::table('gift_cards')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($giftCard, 404);

        DB::table('gift_cards')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.payment-gateways.gift-card')
            ->with('success', 'Gift card deleted successfully.');
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
