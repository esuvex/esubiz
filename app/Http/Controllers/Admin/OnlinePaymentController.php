<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlinePaymentController extends Controller
{

    public function gateways()
    {
        return view('admin.payment-gateways.index');
    }

    public function index()
    {
        $gateways = DB::table('payment_providers')
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return view('admin.online-payment.index', compact('gateways'));
    }

    public function update(Request $request, int $provider)
    {
        $gateway = DB::table('payment_providers')
            ->where('id', $provider)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($gateway, 404);

        $credentials = $request->input('credentials', []);
        $settings = $request->input('settings', []);

        $existingCredentials = $gateway->credentials
            ? json_decode($gateway->credentials, true)
            : [];

        $existingSettings = $gateway->settings
            ? json_decode($gateway->settings, true)
            : [];

        foreach ($credentials as $key => $value) {
            if ($value === null || $value === '') {
                unset($credentials[$key]);
                continue;
            }

            /*
             * Password/secret fields are displayed masked in the
             * admin UI. Never save the mask over the real credential.
             */
            if ((string) $value === '••••••••') {
                unset($credentials[$key]);
                continue;
            }

            $credentials[$key] = trim((string) $value);
        }

        foreach ($settings as $key => $value) {
            $settings[$key] = is_string($value)
                ? trim($value)
                : $value;
        }

        $mergedCredentials = array_merge(
            $existingCredentials,
            $credentials
        );

        $mergedSettings = array_merge(
            $existingSettings,
            $settings
        );

        DB::table('payment_providers')
            ->where('id', $gateway->id)
            ->update([
                'credentials' => json_encode($mergedCredentials),
                'settings' => json_encode($mergedSettings),
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.payment-gateways.online.index')
            ->with('success', "{$gateway->name} settings saved successfully.");
    }
}
