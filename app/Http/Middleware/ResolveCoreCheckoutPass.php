<?php

namespace App\Http\Middleware;

use App\Services\Marketplace\CoreCheckoutPassService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveCoreCheckoutPass
{
    public function handle(Request $request, Closure $next): Response
    {
        $orderId = (int) (
            $request->route('order')
            ?: $request->input('order_id', 0)
        );


        // CORE_OFFLINE_ATTEMPT_ORDER_V1
        if ($request->route('attempt') !== null) {
            $attempt = DB::table('payment_attempts')
                ->where('id', (int) $request->route('attempt'))
                ->whereNull('deleted_at')
                ->first();
            abort_unless($attempt, 404);

            $transaction = DB::table('payment_transactions')
                ->where('id', $attempt->payment_transaction_id)
                ->first();
            abort_unless($transaction, 404);

            $metadata = json_decode($attempt->metadata ?: '{}', true) ?: [];
            $payload = json_decode($transaction->payload ?: '{}', true) ?: [];
            $orderId = (int) ($metadata['marketplace_order_id']
                ?? $payload['marketplace_order_id'] ?? 0);
        }

        $token = (string) $request->input('core_checkout_pass', '');

        if ($token !== '') {
            $request->attributes->set('core_checkout_token', $token);
        }

        if ($token !== '') {
            abort_unless($orderId > 0, 403);
            try {
                $pass = app(CoreCheckoutPassService::class)
                    ->resolve($token, $orderId);
            } catch (\RuntimeException $exception) {
                abort(403, $exception->getMessage());
            }

            $request->session()->put(
                'core_checkout_pass',
                [
                    'id' => (int) $pass->id,
                    'order_id' => $orderId,
                    'token_hash' => hash('sha256', $token),
                ]
            );
        }

        $stored = $request->session()->get('core_checkout_pass');

        if (!is_array($stored)) {
            abort_unless(Auth::check(), 401);
            return $next($request);
        }

        if ($orderId < 1 ||
            (int) ($stored['order_id'] ?? 0) !== $orderId) {
            abort_unless(Auth::check(), 403);
            return $next($request);
        }

        $pass = DB::table('core_checkout_passes')
            ->where('id', (int) ($stored['id'] ?? 0))
            ->where('marketplace_order_id', $orderId)
            ->where('token_hash', (string) ($stored['token_hash'] ?? ''))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        abort_unless($pass, 403, 'This checkout pass has expired.');

        $checkout = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $orderId)
            ->where('website_id', (int) $pass->website_id)
            ->where('checkout_origin', $pass->checkout_origin)
            ->where('wallet_allowed', false)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $order = DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->first();

        abort_unless(
            $checkout && $order
            && (int) $checkout->user_id === (int) $order->buyer_id,
            403,
            'Checkout identity no longer matches this order.'
        );

        if ($request->isMethod('post')) {
            /*
             * The saved checkout session owns the payment target.
             * A checkout pass cannot purchase a different product/order.
             */
            $request->merge([
                'order_id' => $orderId,
                'product_type' => $checkout->product_type,
                'product_id' => (int) $checkout->product_id,
                'quantity' => $checkout->product_type === 'credit_volume'
                    ? 1
                    : $checkout->quantity,
                'website_id' => (int) $checkout->website_id,
                'deployment_type' => $checkout->deployment_type,
                'checkout_context' => $checkout->deployment_type,
                'checkout_origin' => $checkout->checkout_origin,
                'wallet_allowed' => false,
            ]);

            abort_if(
                str_starts_with(
                    strtolower((string) $request->input('payment_option', '')),
                    'wallet'
                ),
                403,
                'Wallet is unavailable for Core checkout.'
            );
        }

        abort_unless(
            Auth::onceUsingId((int) $order->buyer_id),
            403,
            'Checkout buyer is unavailable.'
        );

        $request->attributes->set('core_checkout_order_id', $orderId);

        $checkoutStartedAt = microtime(true);
        $response = $next($request);
        $checkoutElapsedMs = round((microtime(true) - $checkoutStartedAt) * 1000, 1);

        \Illuminate\Support\Facades\Log::info('CORE_CHECKOUT_TIMING', [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'processing_ms' => $checkoutElapsedMs,
        ]);

        $response->headers->set(
            'Server-Timing',
            'checkout;dur=' . $checkoutElapsedMs
        );
        /*
         * Preserve Core access through same-order Central redirects.
         * Never attach the pass to a gateway or another order.
         */
        $coreToken = (string) $request->attributes->get('core_checkout_token', '');
        $location = $response->headers->get('Location');

        if ($coreToken !== '' && is_string($location) && $location !== '') {
            $parts = parse_url($location);
            $centralHost = strtolower(
                (string) parse_url(config('app.url'), PHP_URL_HOST)
            );
            $redirectHost = strtolower((string) ($parts['host'] ?? ''));
            $redirectPath = (string) ($parts['path'] ?? '');

            $sameOrderPath =
                $redirectPath === '/marketplace/checkout/' . $orderId
                || $redirectPath === '/marketplace/orders/' . $orderId . '/payment-status';

            // Attach access only when the offline attempt belongs to this order.
            if (preg_match(
                '#^/marketplace/developer/offline-payment/([0-9]+)$#',
                $redirectPath,
                $offlineMatch
            )) {
                $redirectAttempt = DB::table('payment_attempts')
                    ->where('id', (int) $offlineMatch[1])
                    ->whereNull('deleted_at')
                    ->first();
                $redirectTransaction = $redirectAttempt
                    ? DB::table('payment_transactions')
                        ->where('id', $redirectAttempt->payment_transaction_id)
                        ->first()
                    : null;
                $attemptData = $redirectAttempt
                    ? (json_decode($redirectAttempt->metadata ?: '{}', true) ?: [])
                    : [];
                $transactionData = $redirectTransaction
                    ? (json_decode($redirectTransaction->payload ?: '{}', true) ?: [])
                    : [];
                $sameOrderPath = $redirectTransaction
                    && (int) ($attemptData['marketplace_order_id']
                        ?? $transactionData['marketplace_order_id'] ?? 0) === $orderId;
            }

            $centralDestination =
                $redirectHost === $centralHost
                || (
                    $redirectHost === ''
                    && str_starts_with($location, '/')
                    && !str_starts_with($location, '//')
                );

            if ($sameOrderPath && $centralDestination) {
                $query = [];
                parse_str((string) ($parts['query'] ?? ''), $query);
                $query['core_checkout_pass'] = $coreToken;

                $destination = $redirectPath . '?' . http_build_query($query);
                if ($redirectHost !== '') {
                    $destination = rtrim((string) config('app.url'), '/')
                        . $destination;
                }

                $response->headers->set('Location', $destination);
            }
        }

        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
