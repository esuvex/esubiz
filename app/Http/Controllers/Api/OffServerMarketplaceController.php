<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\MarketplaceCheckoutContextService;
use App\Services\Marketplace\MarketplaceProductResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class OffServerMarketplaceController extends Controller
{
    /**
     * Public read-only catalog consumed by marketplace.esubiz.com.
     *
     * Central remains the single source of truth. Only products available
     * for off-server deployment are returned.
     */
    public function catalog(Request $request, MarketplaceProductResolver $products)
    {
        $requestedType = trim((string) $request->query('type', ''));

        $types = [
            'theme',
            'module',
            'website_type',
        ];

        if ($requestedType !== '' && in_array($requestedType, $types, true)) {
            $types = [$requestedType];
        }

        $items = collect();

        foreach ($types as $type) {
            $rows = DB::table('catalog_products')
                ->where('product_type', $type)
                ->where('is_active', true)
                ->where('is_public', true)
                ->whereIn('audience', ['developer', 'both'])
                ->whereNull('deleted_at')
                ->orderByDesc('is_featured')
                ->orderByDesc('id')
                ->get();

            foreach ($rows as $row) {
                if (!$products->available($row, 'off_server')) {
                    continue;
                }

                $items->push([
                    'type' => $type,
                    'id' => (int) $row->id,
                    'uuid' => $row->uuid ?? null,
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'description' => $row->description,
                    'featured' => (bool) ($row->is_featured ?? false),
                    'price' => $products->price($row, 'off_server'),
                    'currency' => $products->currency($row, 'off_server'),
                    'deployment_type' => 'off_server',
                ]);
            }
        }

        foreach ([
            'addon' => 'core_addons',
            'bundle' => 'core_addon_bundles',
        ] as $type => $table) {
            if ($requestedType !== '' && $requestedType !== $type) {
                continue;
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table)
                ->where('is_active', true)
                ->where('off_server_available', true);

            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            foreach ($query->orderByDesc('id')->get() as $row) {
                if (!$products->available($row, 'off_server')) {
                    continue;
                }

                $items->push([
                    'type' => $type,
                    'id' => (int) $row->id,
                    'uuid' => $row->uuid ?? null,
                    'name' => $row->name ?? $row->title ?? ucfirst($type),
                    'slug' => $row->slug ?? null,
                    'description' => $row->description ?? null,
                    'featured' => false,
                    'price' => $products->price($row, 'off_server'),
                    'currency' => $products->currency($row, 'off_server'),
                    'deployment_type' => 'off_server',
                ]);
            }
        }

        return response()->json([
            'data' => $items->values(),
            'meta' => [
                'deployment_type' => 'off_server',
                'source' => 'esubiz-central',
                'count' => $items->count(),
            ],
        ]);
    }
    /**
     * ============================================================
     * OFF-SERVER CORE -> CENTRAL MARKETPLACE CHECKOUT HANDOFF
     * ============================================================
     *
     * The remote Core authenticates with the installation bearer
     * token provisioned after successful licence activation.
     *
     * The remote website NEVER supplies an authoritative website_id.
     *
     * Central resolves:
     *
     * bearer token
     *   -> current installation
     *   -> registered Central website
     *   -> active domain-locked licence
     *
     * Central then creates the Marketplace order/session itself and
     * returns a temporary Central checkout URL.
     */
    public function checkoutLink(
        Request $request,
        MarketplaceCheckoutContextService $contexts,
        MarketplaceProductResolver $products
    ) {
        $data = $request->validate([
            'product_type' => [
                'required',
                'string',
                'max:100',
            ],

            'product_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],

            'return_url' => [
                'required',
                'url',
                'max:2000',
            ],

            'return_area' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);


        /*
         * This is the authoritative identity resolution.
         *
         * website_id, installation_id and licence registration are
         * derived from the bearer token — never from request input.
         */
        $context =
            $contexts->offServerRequest(
                $request,
                null,
                $data['return_url'],
                $data['return_area']
                    ?? null
            );


        $website =
            \App\Models\Website::query()
                ->findOrFail(
                    $context['website_id']
                );


        /*
         * The Central website owner is the commercial buyer identity.
         *
         * We do NOT create a browser login session here.
         * The resulting temporary checkout URL performs the trusted
         * browser handoff separately.
         */
        abort_unless(
            !empty($website->owner_id),
            403,
            'The registered off-server website has no Central owner.'
        );


        $buyerId =
            (int) $website->owner_id;


        /*
         * Resolve the canonical Marketplace product centrally.
         */
        $product =
            $products->resolve(
                $data['product_type'],
                (int) $data['product_id']
            );


        abort_unless(
            $product,
            404,
            'Marketplace product was not found.'
        );


        abort_unless(
            $products->available(
                $product,
                'off_server'
            ),
            403,
            'This product is not available for off-server websites.'
        );


        $listing =
            DB::table(
                'marketplace_listings'
            )
                ->where(
                    'product_type',
                    $data['product_type']
                )
                ->where(
                    'product_id',
                    (int) $data['product_id']
                )
                ->where(
                    'status',
                    'published'
                )
                ->whereNull(
                    'deleted_at'
                )
                ->latest('id')
                ->first();


        abort_unless(
            $listing,
            404,
            'Marketplace listing was not found.'
        );


        $quantity =
            max(
                1,
                (int) (
                    $data['quantity']
                    ?? 1
                )
            );


        $unitPrice =
            (float) (
                $products->price(
                    $product,
                    'off_server'
                )
                ?? $listing->price
                ?? 0
            );


        $currency =
            strtoupper(
                (string) (
                    $products->currency(
                        $product,
                        'off_server'
                    )
                    ?: (
                        $listing->currency
                        ?? 'NGN'
                    )
                )
            );


        $amount =
            round(
                $unitPrice * $quantity,
                2
            );


        /*
         * Create Central Marketplace order.
         */
        $orderReference =
            'OFF-'
            . strtoupper(
                Str::random(16)
            );


        $orderId =
            DB::table(
                'marketplace_orders'
            )
                ->insertGetId([
                    'marketplace_listing_id' =>
                        $listing->id,

                    'vendor_id' =>
                        $listing->vendor_id,

                    'workspace_id' =>
                        $listing->workspace_id
                        ?? null,

                    'buyer_id' =>
                        $buyerId,

                    'uuid' =>
                        (string) Str::uuid(),

                    'reference' =>
                        $orderReference,

                    'amount' =>
                        $amount,

                    'commission_amount' =>
                        0,

                    'vendor_amount' =>
                        $amount,

                    'currency' =>
                        $currency,

                    'status' =>
                        'pending',

                    'payment_status' =>
                        'pending',

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);


        /*
         * Persist authoritative checkout target.
         *
         * Off-server wallet is ALWAYS false here.
         */
        $context['buyer_user_id'] =
            $buyerId;

        $context['wallet_allowed'] =
            false;


        $sessionValues =
            $contexts->sessionValues(
                $context
            );


        $checkoutSession =
            array_merge(
                [
                    'user_id' =>
                        $buyerId,

                    'account_mode' =>
                        'user',

                    'product_type' =>
                        $data['product_type'],

                    'product_id' =>
                        (int) $data['product_id'],

                    'quantity' =>
                        $quantity,

                    'unit_price' =>
                        $unitPrice,

                    'total_amount' =>
                        $amount,

                    'currency' =>
                        $currency,

                    'marketplace_order_id' =>
                        $orderId,

                    'is_commissionable' =>
                        true,

                    'status' =>
                        'pending_payment',

                    'expires_at' =>
                        now()->addHours(24),

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ],
                $sessionValues
            );


        DB::table(
            'marketplace_checkout_sessions'
        )->insert(
            $checkoutSession
        );


        /*
         * Browser handoff.
         *
         * The temporary signature prevents the remote Core/browser
         * from changing order or buyer identity.
         */
        $checkoutUrl =
            URL::temporarySignedRoute(
                'marketplace.off-server.checkout.handoff',
                now()->addMinutes(30),
                [
                    'order' =>
                        $orderId,

                    'buyer' =>
                        $buyerId,
                ]
            );


        return response()->json([
            'success' =>
                true,

            'order_id' =>
                $orderId,

            'reference' =>
                $orderReference,

            'website_id' =>
                $context['website_id'],

            'website_uuid' =>
                $context['website_uuid'],

            'deployment_type' =>
                'off_server',

            'checkout_origin' =>
                'off_server_website',

            'wallet_allowed' =>
                false,

            'checkout_url' =>
                $checkoutUrl,

            'expires_in_minutes' =>
                30,
        ]);
    }


    /**
     * Volume credits for the registered Core website. Central supplies
     * the website identity and authoritative amount.
     */
    public function creditVolumeCheckoutLink(
        Request $request,
        MarketplaceCheckoutContextService $contexts,
        \App\Services\Marketplace\CreditVolumeCheckoutService $checkouts
    ) {
        $data = $request->validate([
            'credit_type' => [
                'required',
                'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits',
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
            'return_url' => ['required', 'url', 'max:2000'],
        ]);

        $context = $contexts->offServerRequest(
            $request,
            null,
            $data['return_url'],
            'credits'
        );

        $website = \App\Models\Website::query()
            ->where('id', (int) $context['website_id'])
            ->where('deployment_type', 'off_server')
            ->where('status', 'active')
            ->firstOrFail();

        $buyerId = (int) ($website->owner_id ?: $website->developer_id);
        abort_unless($buyerId > 0, 403, 'Core website has no Central buyer.');

        try {
            $orderId = $checkouts->create(
                $website,
                $buyerId,
                $data['credit_type'],
                (int) $data['quantity'],
                'off_server_website',
                (int) $website->developer_id === $buyerId ? 'developer' : 'user',
                $data['return_url']
            );
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'order_id' => $orderId,
            'checkout_url' => URL::temporarySignedRoute(
                'marketplace.off-server.checkout.handoff',
                now()->addMinutes(30),
                ['order' => $orderId, 'buyer' => $buyerId]
            ),
            'expires_in_minutes' => 30,
        ]);
    }


    /**
     * Central payment state for the Core installation that owns this order.
     * The installation bearer token establishes the website identity.
     */
    public function checkoutStatus(
        Request $request,
        int $order,
        MarketplaceCheckoutContextService $contexts
    ) {
        $context = $contexts->offServerRequest($request);

        $session = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $order)
            ->where('website_id', (int) $context['website_id'])
            ->where('checkout_origin', 'off_server_website')
            ->where('deployment_type', 'off_server')
            ->latest('id')
            ->first();

        abort_unless($session, 404);

        $record = DB::table('marketplace_orders')
            ->where('id', $order)
            ->where('buyer_id', (int) $session->user_id)
            ->first();

        abort_unless($record, 404);

        /*
         * Reconcile confirmed Central payment before reporting status to
         * the authenticated Core installation. Lock the order so two
         * simultaneous status requests cannot grant the product twice.
         */
        if ($record->payment_status === 'paid') {
            DB::transaction(function () use ($order, $session) {
                $paidOrder = DB::table('marketplace_orders')
                    ->where('id', $order)
                    ->lockForUpdate()
                    ->first();

                abort_unless($paidOrder, 404);

                if ($paidOrder->payment_status !== 'paid' ||
                    in_array((string) $paidOrder->status, ['completed', 'fulfilled'], true)) {
                    return;
                }

                if ((string) $session->product_type === 'credit_volume') {
                    app(
                        \App\Services\Marketplace\CreditVolumeFulfilmentService::class
                    )->fulfil($paidOrder);
                } else {
                $listing = DB::table('marketplace_listings')
                    ->where('id', $paidOrder->marketplace_listing_id)
                    ->first();

                abort_unless($listing, 422, 'Paid order has no Marketplace listing.');
                abort_unless(
                    (string) $listing->product_type === (string) $session->product_type &&
                    (int) $listing->product_id === (int) $session->product_id,
                    422,
                    'Marketplace order and Core checkout session do not match.'
                );

                $orderForFulfilment = (object) array_merge(
                    (array) $paidOrder,
                    [
                        'deployment_type' => 'off_server',
                        'website_id' => (int) $session->website_id,
                        'workspace_id' => $session->workspace_id
                            ?? $paidOrder->workspace_id
                            ?? null,
                        'checkout_origin' => 'off_server_website',
                        'return_url' => $session->return_url ?? null,
                        'return_area' => $session->return_area ?? null,
                        'wallet_allowed' => false,
                    ]
                );

                app(
                    \App\Services\Marketplace\MarketplaceFulfilmentManager::class
                )->fulfil($orderForFulfilment, $listing);
                }

                DB::table('marketplace_orders')
                    ->where('id', $paidOrder->id)
                    ->update(['status' => 'completed', 'updated_at' => now()]);

                DB::table('marketplace_checkout_sessions')
                    ->where('id', $session->id)
                    ->update(['status' => 'completed', 'updated_at' => now()]);
            });

            $record = DB::table('marketplace_orders')
                ->where('id', $order)
                ->first();
        }

        return response()->json([
            'order_id' => (int) $record->id,
            'reference' => $record->reference,
            'product_type' => $session->product_type,
            'product_id' => (int) $session->product_id,
            'amount' => $record->amount,
            'currency' => $record->currency,
            'payment_status' => $record->payment_status,
            'order_status' => $record->status,
            'paid' => $record->payment_status === 'paid',
            'fulfilled' => in_array($record->status, ['fulfilled', 'completed'], true),
        ]);
    }


    /**
     * Trusted browser handoff after the Core API has created
     * the order/session.
     */
    public function handoff(
        Request $request,
        int $order
    ) {
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This Marketplace checkout link is invalid or has expired.'
        );


        $record =
            DB::table(
                'marketplace_orders'
            )
                ->where(
                    'id',
                    $order
                )
                ->firstOrFail();


        $session =
            DB::table(
                'marketplace_checkout_sessions'
            )
                ->where(
                    'marketplace_order_id',
                    $record->id
                )
                ->latest('id')
                ->first();


        abort_unless(
            $session,
            404,
            'Marketplace checkout session was not found.'
        );


        abort_unless(
            ($session->checkout_origin ?? null)
                === 'off_server_website'
                &&
            ($session->deployment_type ?? null)
                === 'off_server',
            403,
            'Marketplace checkout identity is invalid.'
        );


        abort_unless(
            (int) $record->buyer_id
                ===
            (int) $request->query(
                'buyer'
            ),
            403,
            'Marketplace buyer identity is invalid.'
        );


        /*
         * The installation bearer was verified when Central created this
         * order. Give this browser access to this order only; never sign
         * it into the website owner's Central account.
         */
        $pass = app(
            \App\Services\Marketplace\CoreCheckoutPassService::class
        )->issue(
            (int) $record->id,
            (int) $session->website_id,
            'off_server_website'
        );

        return redirect()->route(
            'marketplace.checkout',
            [
                'order' => $record->id,
                'core_checkout_pass' => $pass,
            ]
        );
    }
}
