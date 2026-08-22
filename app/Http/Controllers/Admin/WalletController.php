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

        return view('admin.payment-gateways.wallet', [
            'settings' => $settings,
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
