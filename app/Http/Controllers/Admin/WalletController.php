<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $settings = DB::table('wallet_settings')
            ->whereNull('workspace_id')
            ->first();

        if (!$settings) {
            DB::table('wallet_settings')->insert([
                'workspace_id' => null,
                'funding_enabled' => true,
                'minimum_funding' => 0,
                'maximum_funding' => null,
                'funding_fee_type' => 'free',
                'funding_fee' => 0,
                'payout_enabled' => true,
                'minimum_payout' => 0,
                'maximum_payout' => null,
                'payout_fee_type' => 'free',
                'payout_fee' => 0,
                'wallet_transfer_enabled' => true,
                'wallet_transfer_fee_type' => 'free',
                'wallet_transfer_fee' => 0,
                'payout_cycle' => 'instant',
                'approval_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $settings = DB::table('wallet_settings')
                ->whereNull('workspace_id')
                ->first();
        }

        /*
         * Wallet Funding Records
         *
         * Funding attempts are financial audit records, not platform
         * revenue. Keep them visible to Admin regardless of whether the
         * funding method is online, offline or Gift Card.
         */
        $fundings = DB::table('wallet_fundings as fundings')
            ->leftJoin(
                'wallets',
                'wallets.id',
                '=',
                'fundings.wallet_id'
            )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'fundings.user_id'
            )
            ->whereNull('fundings.deleted_at')
            ->select([
                'fundings.id',
                'fundings.reference',
                'fundings.amount',
                'fundings.fee',
                'fundings.net_amount',
                'fundings.currency',
                'fundings.method',
                'fundings.gateway_reference',
                'fundings.status',
                'fundings.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'wallets.uuid as wallet_uuid',
            ])
            ->orderByDesc('fundings.created_at')
            ->paginate(10, ['*'], 'funding_page');

        /*
         * Canonical Wallet Transaction Log
         *
         * This is the ledger of completed wallet movements. It remains
         * separate from funding attempts so Admin can audit both the
         * payment/funding lifecycle and the resulting balance movement.
         */
        $transactions = DB::table(
                'wallet_transactions as transactions'
            )
            ->leftJoin(
                'wallets',
                'wallets.id',
                '=',
                'transactions.wallet_id'
            )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'transactions.user_id'
            )
            ->whereNull('transactions.deleted_at')
            ->select([
                'transactions.id',
                'transactions.reference',
                'transactions.type',
                'transactions.direction',
                'transactions.amount',
                'transactions.balance_before',
                'transactions.balance_after',
                'transactions.currency',
                'transactions.status',
                'transactions.description',
                'transactions.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'wallets.uuid as wallet_uuid',
            ])
            ->orderByDesc('transactions.created_at')
            ->paginate(10, ['*'], 'transaction_page');

        return view('admin.payment-gateways.wallet', [
            'settings' => $settings,
            'fundings' => $fundings,
            'transactions' => $transactions,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'minimum_funding' => ['nullable', 'numeric', 'min:0'],
            'maximum_funding' => ['nullable', 'numeric', 'min:0'],
            'funding_fee_type' => ['nullable', 'in:free,fixed,percentage'],
            'funding_fee' => ['nullable', 'numeric', 'min:0'],

            'minimum_payout' => ['nullable', 'numeric', 'min:0'],
            'maximum_payout' => ['nullable', 'numeric', 'min:0'],
            'payout_fee_type' => ['nullable', 'in:free,fixed,percentage'],
            'payout_fee' => ['nullable', 'numeric', 'min:0'],

            'payout_cycle' => [
                'nullable',
                'in:instant,manual,daily,weekly,monthly'
            ],
        ]);

        DB::table('wallet_settings')
            ->whereNull('workspace_id')
            ->update([
                'funding_enabled' => $request->boolean('funding_enabled'),

                'minimum_funding' =>
                    $data['minimum_funding'] ?? 0,

                'maximum_funding' =>
                    $data['maximum_funding'] ?? null,

                'funding_fee_type' =>
                    empty($data['funding_fee'])
                        ? 'free'
                        : ($data['funding_fee_type'] ?? 'fixed'),

                'funding_fee' =>
                    empty($data['funding_fee'])
                        ? 0
                        : $data['funding_fee'],

                'payout_enabled' =>
                    $request->boolean('payout_enabled'),

                'minimum_payout' =>
                    $data['minimum_payout'] ?? 0,

                'maximum_payout' =>
                    $data['maximum_payout'] ?? null,

                'payout_fee_type' =>
                    empty($data['payout_fee'])
                        ? 'free'
                        : ($data['payout_fee_type'] ?? 'fixed'),

                'payout_fee' =>
                    empty($data['payout_fee'])
                        ? 0
                        : $data['payout_fee'],

                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Wallet settings updated successfully.'
        );
    }
}
