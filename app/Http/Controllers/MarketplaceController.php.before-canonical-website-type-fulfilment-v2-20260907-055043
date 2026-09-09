<?php

namespace App\Http\Controllers;

use App\Services\Marketplace\Themes\ThemeMarketplaceResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index()
    {
        return view('marketplace.index', [
            'addons' => DB::table('core_addons')
                ->where('is_active', true)
                ->where('saas_available', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundles' => DB::table('core_addon_bundles')
                ->where('is_active', true)
                ->where('saas_available', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'catalogProducts' => $this->catalogProductsFor('saas'),

            'bundleItems' => DB::table('core_addon_bundle_items as items')
                ->join('core_addons as addons', 'addons.id', '=', 'items.addon_id')
                ->whereIn(
                    'items.bundle_id',
                    DB::table('core_addon_bundles')
                        ->where('is_active', true)
                        ->where('saas_available', true)
                        ->whereNull('deleted_at')
                        ->pluck('id')
                )
                ->where('addons.is_active', true)
                ->whereNull('addons.deleted_at')
                ->select(
                    'items.bundle_id',
                    'addons.id as addon_id',
                    'addons.name',
                    'items.allocation',
                    'items.is_unlimited'
                )
                ->orderBy('addons.name')
                ->get()
                ->groupBy('bundle_id'),
        ]);
    }


    public function developerCheckout(string $productType, int $productId)
    {
        /*
         * Developer checkout is marketplace-product agnostic.
         *
         * Resolve the published marketplace listing first so future
         * product types such as themes, modules and website types can
         * use the same checkout architecture.
         */
        $listing = $this->resolveMarketplaceListing(
            $productType,
            $productId
        );

        abort_unless($listing, 404);

        $table = match ($listing->product_type) {
            'core_addon' => 'core_addons',
            'core_bundle' => 'core_addon_bundles',
            default => null,
        };

        /*
         * Core products currently have their source records in the
         * central Core tables. Future marketplace product types can
         * provide their own source resolution without changing checkout.
         */
        $product = $table
            ? DB::table($table)
                ->where('id', $listing->product_id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first()
            : null;

        abort_unless($product, 404);

        /*
         * Marketplace listing is the commercial source of truth.
         * It contains the published title, description, price and
         * currency shown to the buyer.
         */
        /*
         * Developer/off-server checkout must use the Core product's
         * off-server price, not the marketplace listing's SaaS price.
         *
         * SaaS and developer purchases intentionally have independent
         * pricing.
         */
        $price = (float) ($product->off_server_price ?? $listing->price ?? 0);
        $currency = $product->off_server_currency
            ?? $listing->currency
            ?? 'NGN';

        /*
         * Core bundles expose their included addons through the
         * bundle-items table. Load these so the checkout can show
         * exactly what the buyer receives, including allocations
         * configured by the administrator.
         */
        /*
         * Included product items.
         *
         * Bundles expose their included addons through the bundle-items
         * table. A standalone addon is itself the included product, so
         * expose it through the same collection used by the checkout.
         */
        $includedItems = collect();

        if ($listing->product_type === 'core_bundle') {
            $includedItems = DB::table('core_addon_bundle_items as items')
                ->join(
                    'core_addons as addons',
                    'addons.id',
                    '=',
                    'items.addon_id'
                )
                ->join(
                    'core_addon_bundles as bundles',
                    'bundles.id',
                    '=',
                    'items.bundle_id'
                )
                ->where('items.bundle_id', $listing->product_id)
                ->where('addons.is_active', true)
                ->whereNull('addons.deleted_at')
                ->select(
                    'addons.id',
                    'addons.name',
                    'addons.description',
                    'addons.default_allocation',
                    'addons.allocation_unit',
                    'addons.is_unlimited',
                    'items.allocation',
                    'items.is_unlimited as bundle_is_unlimited',
                    'bundles.unit_name as bundle_unit_name'
                )
                ->orderBy('addons.name')
                ->get();
        } elseif ($listing->product_type === 'core_addon') {
            $capabilityAllocations = DB::table('core_addon_capability_allocations')
                ->where('addon_id', $product->id)
                ->orderBy('id')
                ->get();

            $includedItems = $capabilityAllocations->map(function ($allocation) use ($product) {
                $key = (string) $allocation->capability_key;

                return (object) [
                    'id' => $allocation->id,
                    'name' => ucwords(
                        str_replace(
                            ['_', '-'],
                            ' ',
                            preg_replace('/^crm_/', '', $key)
                        )
                    ),
                    'description' => null,
                    'default_allocation' => null,
                    'allocation_unit' => $product->allocation_unit,
                    'is_unlimited' => (bool) $allocation->is_unlimited,
                    'allocation' => $allocation->allocation,
                    'bundle_is_unlimited' => false,
                ];
            });

            /*
             * If an addon has no capability allocation records, keep the
             * addon itself visible as the included product.
             */
            if ($includedItems->isEmpty()) {
                $includedItems = collect([
                    (object) [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'default_allocation' => $product->default_allocation,
                        'allocation_unit' => $product->allocation_unit,
                        'is_unlimited' => $product->is_unlimited,
                        'allocation' => null,
                        'bundle_is_unlimited' => false,
                    ],
                ]);
            }
        }

        $marketplacePaymentContext =
            session('account_mode') === 'developer'
                ? 'developer_marketplace_checkout'
                : 'user_marketplace_checkout';

        $onlineGateways = DB::table('payment_providers as providers')
            ->join(
                'payment_methods as methods',
                'methods.payment_provider_id',
                '=',
                'providers.id'
            )
            ->where('providers.is_active', true)
            ->whereNull('providers.deleted_at')
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->select(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type',
                'providers.usage_contexts'
            )
            ->groupBy(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type',
                'providers.usage_contexts'
            )
            ->orderBy('providers.name')
            ->get()
            ->filter(function ($provider) use ($marketplacePaymentContext) {
                if ($provider->usage_contexts === null) {
                    return true;
                }

                $contexts = json_decode(
                    $provider->usage_contexts,
                    true
                );

                return is_array($contexts)
                    && in_array(
                        $marketplacePaymentContext,
                        $contexts,
                        true
                    );
            })
            ->values();

        $offlineMethods = DB::table('offline_payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->filter(function ($method) use ($marketplacePaymentContext) {
                if ($method->usage_contexts === null) {
                    return true;
                }

                $contexts = json_decode(
                    $method->usage_contexts,
                    true
                );

                return is_array($contexts)
                    && in_array(
                        $marketplacePaymentContext,
                        $contexts,
                        true
                    );
            })
            ->values();

        /*
         * Unified checkout entry.
         *
         * Developer/off-server purchases now create their pending order
         * before checkout, exactly like SaaS purchases, then enter the
         * single marketplace.checkout route.
         */
        $quantity = max(1, (int) request()->query('quantity', 1));
        $amount = (float) $price * $quantity;

        $orderReference = 'MKT-' . strtoupper(
            \Illuminate\Support\Str::random(12)
        );

        $orderId = DB::table('marketplace_orders')->insertGetId([
            'marketplace_listing_id' => $listing->id,
            'vendor_id' => $listing->vendor_id,
            'workspace_id' => $listing->workspace_id ?? null,
            'buyer_id' => auth()->id(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference' => $orderReference,
            'amount' => $amount,
            'commission_amount' => 0,
            'vendor_amount' => $amount,
            'currency' => strtoupper($currency ?: 'NGN'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('marketplace_checkout_sessions')->insert([
            'user_id' => auth()->id(),
            'account_mode' => 'developer',
            'product_type' => $productType,
            'product_id' => $productId,
            'deployment_type' => 'off_server',
            'workspace_id' => $listing->workspace_id ?? null,
            'quantity' => $quantity,
            'unit_price' => (float) $price,
            'total_amount' => $amount,
            'currency' => strtoupper($currency ?: 'NGN'),
            'marketplace_order_id' => $orderId,
            'is_commissionable' => true,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('marketplace.checkout', [
            'order' => $orderId,
        ]);
    }

    /**
     * Unified marketplace payment entry point.
     *
     * The checkout UI is shared across SaaS, off-server/developer and
     * future marketplace products. The payment context determines which
     * existing fulfilment/payment flow handles the transaction.
     *
     * Product availability itself remains controlled by the product resolver
     * and canonical marketplace product configuration.
     */
    public function marketplacePayment(Request $request)
    {
        /*
         * Never allow an already-paid Marketplace order to enter any
         * payment processor again. This protects online, offline,
         * Wallet and Gift Card payments from accidental double payment.
         */
        if ($request->filled('order_id')) {
            $existingOrder = DB::table('marketplace_orders')
                ->where('id', (int) $request->input('order_id'))
                ->where('buyer_id', auth()->id())
                ->first();

            abort_unless($existingOrder, 404);

            if (
                $existingOrder->payment_status === 'paid'
                || in_array(
                    $existingOrder->status,
                    ['completed', 'fulfilled'],
                    true
                )
            ) {
                return redirect()->route(
                    'marketplace.payment-status',
                    ['order' => $existingOrder->id]
                )->with(
                    'status',
                    'This order has already been paid.'
                );
            }
        }

        $context = $request->input('checkout_context');
        $deploymentType = $request->input('deployment_type');

        /*
         * Normalize the shared checkout context.
         *
         * Product-specific checkout remains extensible:
         * - saas        = SaaS-only purchase
         * - off_server  = developer/off-server purchase
         * - both        = product supports either context
         *
         * The selected deployment type determines the actual fulfilment
         * path for products that support both.
         */
        if ($context === 'both') {
            $context = $deploymentType;
        }

        if (!$context && in_array($deploymentType, ['saas', 'off_server'], true)) {
            $context = $deploymentType;
        }

        /*
         * One central checkout entry point.
         *
         * The payment implementation remains deployment-aware internally,
         * while the public checkout form and endpoint remain identical for
         * SaaS, Developer/off-server and future shared products.
         */
        $paymentOption = (string) $request->input('payment_option', '');
        [$paymentMode] = array_pad(explode(':', $paymentOption, 2), 2, null);

        if ($paymentMode === 'offline') {
            return $this->processUnifiedOfflineMarketplacePayment(
                $request,
                $context ?: $deploymentType
            );
        }

        if ($paymentMode === 'wallet' || $paymentOption === 'wallet') {
            return $this->processUnifiedWalletMarketplacePayment(
                $request,
                $context ?: $deploymentType
            );
        }

        if ($paymentMode === 'giftcard' || $paymentMode === 'gift_card') {
            return $this->processUnifiedGiftCardMarketplacePayment(
                $request,
                $context ?: $deploymentType
            );
        }

        return $this->processUnifiedMarketplacePayment(
            $request,
            $context ?: $deploymentType
        );
    }

    public function developerCheckoutSubmit(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'max:100'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_option' => ['required', 'string', 'max:255'],
        ]);

        /*
         * ESUBIZ_DEVELOPER_BUILD_EXISTING_ORDER_PAYMENT_V1
         *
         * A compiled Developer Build is not a Marketplace listing.
         * Builder compilation already created its canonical pending order
         * and checkout session. Payment must reuse those records rather
         * than manufacture a second Marketplace order/session.
         */
        $isDeveloperBuild =
            $data['product_type'] === 'developer_build';

        $developerBuild = null;
        $existingDeveloperOrder = null;
        $existingDeveloperCheckoutSession = null;
        $listing = null;
        $product = null;

        if ($isDeveloperBuild) {
            abort_unless(
                (int) $data['quantity'] === 1,
                422,
                'Developer Build quantity must be 1.'
            );

            $developerBuild = \App\Models\DeveloperBuild::query()
                ->where('id', (int) $data['product_id'])
                ->where('developer_id', auth()->id())
                ->first();

            abort_unless($developerBuild, 404);

            abort_unless(
                $developerBuild->status === 'success'
                && !empty($developerBuild->package_reference),
                422,
                'Developer Build must be successfully compiled before payment.'
            );

            $existingDeveloperOrder = DB::table('marketplace_orders')
                ->where(
                    'developer_build_id',
                    $developerBuild->id
                )
                ->where('buyer_id', auth()->id())
                ->where('payment_status', 'pending')
                ->latest('id')
                ->first();

            abort_unless(
                $existingDeveloperOrder,
                404,
                'Developer Build payment order was not found.'
            );

            $existingDeveloperCheckoutSession =
                DB::table('marketplace_checkout_sessions')
                    ->where(
                        'marketplace_order_id',
                        $existingDeveloperOrder->id
                    )
                    ->where('user_id', auth()->id())
                    ->where('product_type', 'developer_build')
                    ->where(
                        'product_id',
                        $developerBuild->id
                    )
                    ->where(
                        'deployment_type',
                        'off_server'
                    )
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

            abort_unless(
                $existingDeveloperCheckoutSession,
                404,
                'Developer Build checkout session was not found.'
            );

            $unitPrice =
                (float) $existingDeveloperOrder->amount;

            $amount =
                (float) $existingDeveloperOrder->amount;

            $currency =
                $existingDeveloperOrder->currency
                ?: 'NGN';

        } else {
            $listingProductType = match ($data['product_type']) {
                'addon', 'core_addon', 'core-addon' => 'core_addon',
                'bundle', 'core_bundle', 'core-bundle' => 'core_bundle',
                default => $data['product_type'],
            };

            $listing = $this->resolveMarketplaceListing(
                $listingProductType,
                (int) $data['product_id']
            );

            $productTable = match ($data['product_type']) {
                'addon', 'core_addon', 'core-addon' => 'core_addons',
                'bundle', 'core_bundle', 'core-bundle' => 'core_addon_bundles',
                default => null,
            };

            if (!$productTable) {
                abort(
                    422,
                    'Unsupported marketplace product type.'
                );
            }

            $product = DB::table($productTable)
                ->where('id', $data['product_id'])
                ->where('is_active', true)
                ->where('off_server_available', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless($product, 404);

            if (!$listing) {
                $listing = $this->resolveMarketplaceListing(
                    $listingProductType,
                    (int) $product->id
                );
            }

            abort_unless(
                $listing,
                422,
                'This developer product is not yet available as a marketplace listing.'
            );

            $unitPrice =
                (float) ($product->off_server_price ?? 0);

            $amount =
                $unitPrice * (int) $data['quantity'];

            $currency =
                $product->off_server_currency ?? 'NGN';
        }

        $optionParts = explode(':', $data['payment_option'], 2);

        abort_unless(
            count($optionParts) === 2 &&
            in_array($optionParts[0], ['online', 'offline'], true),
            422,
            'Invalid payment option.'
        );

        [$paymentMode, $paymentIdentifier] = $optionParts;

        $paymentProvider = null;
        $paymentMethod = null;

        if ($paymentMode === 'online') {
            $paymentProvider = DB::table('payment_providers')
                ->where('slug', $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentProvider,
                422,
                'Selected payment gateway is unavailable.'
            );

            if ($paymentProvider->usage_contexts !== null) {
                $contexts = json_decode(
                    $paymentProvider->usage_contexts,
                    true
                );

                abort_unless(
                    is_array($contexts)
                    && in_array(
                        'developer_marketplace_checkout',
                        $contexts,
                        true
                    ),
                    422,
                    'This gateway is not enabled for Developer Marketplace Checkout.'
                );
            }
        }

        if ($paymentMode === 'offline') {
            $paymentMethod = DB::table('offline_payment_methods')
                ->where('id', (int) $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentMethod,
                422,
                'Selected offline payment method is unavailable.'
            );

            if ($paymentMethod->usage_contexts !== null) {
                $contexts = json_decode(
                    $paymentMethod->usage_contexts,
                    true
                );

                abort_unless(
                    is_array($contexts)
                    && in_array(
                        'developer_marketplace_checkout',
                        $contexts,
                        true
                    ),
                    422,
                    'This offline payment method is not enabled for Developer Marketplace Checkout.'
                );
            }
        }

        /*
         * Calculate the actual amount payable through the selected
         * Online or Offline payment method.
         *
         * Marketplace product/order value remains canonical.
         * Only the payment obligation is converted and marked up.
         */
        $gatewayMethod =
            $paymentMode === 'online'
                ? $paymentProvider
                : $paymentMethod;

        try {
            $paymentCalculation = app(
                \App\Services\Core\PaymentConversionService::class
            )->calculate(
                $gatewayMethod,
                strtoupper($currency ?: 'NGN'),
                (float) $amount
            );
        } catch (\Throwable $e) {
            abort(
                422,
                $e->getMessage()
            );
        }

        $gatewayAmount = (float) (
            $paymentCalculation['payment_amount']
                ?? $amount
        );

        $gatewayCurrency = strtoupper(
            $paymentCalculation['to_currency']
                ?? $currency
                ?? 'NGN'
        );

        $orderReference = 'DEV-' . strtoupper(
            \Illuminate\Support\Str::random(12)
        );

        /*
         * Developer/off-server marketplace purchases do not require an
         * Esubiz workspace. Their financial context is the marketplace
         * order itself.
         *
         * This is intentionally separate from User/SaaS checkout.
         */
        $developerWorkspaceId =
            $isDeveloperBuild
                ? ($existingDeveloperOrder->workspace_id ?? null)
                : ($listing->workspace_id ?? null);

        if ($isDeveloperBuild) {
            $pendingOrder =
                (int) $existingDeveloperOrder->id;

            $orderReference =
                $existingDeveloperOrder->reference;

            $checkoutSessionId =
                (int) $existingDeveloperCheckoutSession->id;

            DB::table('marketplace_checkout_sessions')
                ->where('id', $checkoutSessionId)
                ->update([
                    'payment_provider_id' =>
                        $paymentProvider?->id,

                    'payment_method_id' =>
                        $paymentMethod?->id,

                    'updated_at' => now(),
                ]);

        } else {
            $pendingOrder =
                DB::table('marketplace_orders')
                    ->insertGetId([
                        'marketplace_listing_id' => $listing->id,
                        'vendor_id' => $listing->vendor_id,
                        'workspace_id' => $developerWorkspaceId,
                        'buyer_id' => auth()->id(),
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'reference' => $orderReference,
                        'amount' => $amount,
                        'commission_amount' => 0,
                        'vendor_amount' => $amount,
                        'currency' => $currency,
                        'payment_status' => 'pending',
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

            $checkoutSessionId =
                DB::table('marketplace_checkout_sessions')
                    ->insertGetId([
                        'user_id' => auth()->id(),
                        'account_mode' => 'developer',
                        'product_type' => $data['product_type'],
                        'product_id' => $data['product_id'],
                        'quantity' => (int) $data['quantity'],
                        'unit_price' => $unitPrice,
                        'total_amount' => $amount,
                        'currency' => $currency,
                        'payment_method_id' => null,
                        'payment_provider_id' => $paymentProvider?->id,
                        'marketplace_order_id' => $pendingOrder,
                        'deployment_type' => 'off_server',
                        'status' => 'pending_payment',
                        'is_commissionable' => true,
                        'expires_at' => now()->addHours(24),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
        }

        $transactionReference = 'DEV-TXN-' . strtoupper(
            \Illuminate\Support\Str::random(12)
        );

        $paymentTransactionId = DB::table('payment_transactions')
            ->insertGetId([
                'workspace_id' => $developerWorkspaceId,
                'wallet_id' => null,
                'payment_provider_id' => $paymentProvider?->id,
                'reference' => $transactionReference,
                'amount' => $gatewayAmount,
                'currency' => $gatewayCurrency,
                'status' => 'pending',
                'payload' => json_encode([
                    'marketplace_order_id' => $pendingOrder,
                    'marketplace_order_reference' => $orderReference,
                    'payment_mode' => $paymentMode,
                    'payment_provider' => $paymentProvider?->slug,
                    'payment_provider_id' => $paymentProvider?->id,
                    'offline_payment_method_id' => $paymentMethod?->id,
                    'offline_payment_method' => $paymentMethod?->slug,
                    'product_type' => $data['product_type'],
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'buyer_id' => auth()->id(),

                    /*
                     * Canonical Marketplace value.
                     */
                    'source_amount' =>
                        (float) $amount,

                    'source_currency' =>
                        strtoupper(
                            $currency ?: 'NGN'
                        ),

                    /*
                     * Actual amount the payment method requires.
                     */
                    'gateway_amount' =>
                        $gatewayAmount,

                    'gateway_currency' =>
                        $gatewayCurrency,

                    /*
                     * Includes conversion rate, provider,
                     * converted amount and markup breakdown.
                     */
                    'payment_conversion' =>
                        $paymentCalculation,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('marketplace_checkout_sessions')
            ->where('id', $checkoutSessionId)
            ->update([
                'payment_transaction_id' => $paymentTransactionId,
                'updated_at' => now(),
            ]);

        /*
         * Create the payment attempt before contacting the gateway.
         */
        $attemptService = app(
            \App\Services\Payment\CorePaymentAttemptService::class
        );

        $attemptMetadata = [
            'marketplace_order_id' => $pendingOrder,
            'marketplace_order_reference' => $orderReference,
            'product_type' => $data['product_type'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'buyer_id' => auth()->id(),
            'payment_mode' => $paymentMode,

            'offline_payment_method_id' =>
                $paymentMethod?->id,

            'source_amount' =>
                (float) $amount,

            'source_currency' =>
                strtoupper(
                    $currency ?: 'NGN'
                ),

            'gateway_amount' =>
                $gatewayAmount,

            'gateway_currency' =>
                $gatewayCurrency,

            'payment_conversion' =>
                $paymentCalculation,
        ];

        $attempt = $attemptService->create(
            (int) $paymentTransactionId,
            $paymentProvider?->id,
            null,
            $gatewayAmount,
            $attemptMetadata
        );

        /*
         * ONLINE:
         * Initialize the selected gateway and redirect directly to
         * the gateway's hosted payment page.
         */
        if ($paymentMode === 'online') {
            $gatewayManager = app(
                \App\Services\Payment\CorePaymentGatewayManager::class
            );

            $gateway = $gatewayManager->resolve(
                $paymentProvider->slug
            );

            $customer = DB::table('users')
                ->where('id', auth()->id())
                ->first();

            $providerCredentials = $paymentProvider->credentials
                ? (json_decode($paymentProvider->credentials, true) ?: [])
                : [];

            $result = $gateway->initialize(
                DB::table('payment_transactions')
                    ->where('id', $paymentTransactionId)
                    ->first(),
                $attempt,
                [
                    'payment_provider' => $paymentProvider->slug,
                    'secret_key' => $providerCredentials['secret_key'] ?? null,
                    'public_key' => $providerCredentials['public_key'] ?? null,
                    'email' => $customer->email ?? null,
                    'customer_name' => $customer->name ?? null,
                    'callback_url' => route(
                        'marketplace.payment-status',
                        ['order' => $pendingOrder]
                    ),
                    'return_url' => route(
                        'marketplace.payment-status',
                        ['order' => $pendingOrder]
                    ),
                ]
            );

            $attemptService->update(
                (int) $attempt->id,
                'processing',
                $result['gateway_reference']
                    ?? $result['reference']
                    ?? null,
                $result
            );

            $authorizationUrl =
                $result['authorization_url']
                ?? $result['link']
                ?? null;

            abort_unless(
                $authorizationUrl,
                502,
                'Payment gateway did not return a checkout URL.'
            );

            return redirect()->away($authorizationUrl);
        }

        /*
         * OFFLINE:
         * Leave the transaction pending. The offline payment review
         * workflow will confirm or reject it.
         */
        return redirect()
            ->route('marketplace.developer.offline-payment', [
                'attempt' => $attempt->id,
            ])
            ->with(
                'status',
                'Offline payment selected. Complete the payment using the instructions below.'
            );
    }


    public function developerOfflinePayment(int $attempt)
    {
        $attemptRecord = DB::table('payment_attempts')
            ->where('id', $attempt)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($attemptRecord, 404);

        $transaction = DB::table('payment_transactions')
            ->where('id', $attemptRecord->payment_transaction_id)
            ->first();

        abort_unless($transaction, 404);

        $metadata = $attemptRecord->metadata
            ? json_decode($attemptRecord->metadata, true)
            : [];

        $payload = $transaction->payload
            ? json_decode($transaction->payload, true)
            : [];

        $paymentContext = $metadata['payment_context']
            ?? $payload['payment_context']
            ?? $payload['payment_mode']
            ?? null;

        $isWalletFunding =
            $paymentContext === 'wallet_funding'
            || !empty($metadata['wallet_funding_id'])
            || !empty($payload['wallet_funding_id']);

        $ownerId = $isWalletFunding
            ? (
                $metadata['user_id']
                ?? $payload['user_id']
                ?? null
            )
            : (
                $metadata['buyer_id']
                ?? $payload['buyer_id']
                ?? null
            );

        abort_unless(
            (int) $ownerId === (int) auth()->id(),
            403
        );

        $paymentMethodId = $attemptRecord->payment_method_id
            ?? $metadata['offline_payment_method_id']
            ?? $payload['offline_payment_method_id']
            ?? null;

        $paymentMethod = DB::table('offline_payment_methods')
            ->where('id', $paymentMethodId)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        $order = null;
        $funding = null;

        if ($isWalletFunding) {
            $walletFundingId = $metadata['wallet_funding_id']
                ?? $payload['wallet_funding_id']
                ?? null;

            $funding = $walletFundingId
                ? \App\Models\WalletFunding::query()
                    ->where('id', (int) $walletFundingId)
                    ->where('user_id', auth()->id())
                    ->first()
                : null;

            abort_unless($funding, 404);
        } else {
            $orderId = $metadata['marketplace_order_id']
                ?? $payload['marketplace_order_id']
                ?? null;

            $order = $orderId
                ? DB::table('marketplace_orders')
                    ->where('id', $orderId)
                    ->where('buyer_id', auth()->id())
                    ->first()
                : null;

            abort_unless($order, 404);
        }

        return view('marketplace.developer-offline-payment', [
            'attempt' => $attemptRecord,
            'transaction' => $transaction,
            'paymentMethod' => $paymentMethod,
            'metadata' => array_merge($payload, $metadata),
            'order' => $order,
            'funding' => $funding,
            'isWalletFunding' => $isWalletFunding,
        ]);
    }

    public function submitOfflinePaymentReceipt(
        Request $request,
        int $attempt
    ) {
        $attemptRecord = DB::table('payment_attempts')
            ->where('id', $attempt)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($attemptRecord, 404);

        $transaction = DB::table('payment_transactions')
            ->where('id', $attemptRecord->payment_transaction_id)
            ->first();

        abort_unless($transaction, 404);

        $metadata = $attemptRecord->metadata
            ? json_decode($attemptRecord->metadata, true)
            : [];

        $payload = $transaction->payload
            ? json_decode($transaction->payload, true)
            : [];

        $paymentContext = $metadata['payment_context']
            ?? $payload['payment_context']
            ?? $payload['payment_mode']
            ?? null;

        $isWalletFunding =
            $paymentContext === 'wallet_funding'
            || !empty($metadata['wallet_funding_id'])
            || !empty($payload['wallet_funding_id']);

        $ownerId = $isWalletFunding
            ? (
                $metadata['user_id']
                ?? $payload['user_id']
                ?? null
            )
            : (
                $metadata['buyer_id']
                ?? $payload['buyer_id']
                ?? null
            );

        abort_unless(
            (int) $ownerId === (int) auth()->id(),
            403
        );

        abort_unless(
            in_array(
                $attemptRecord->status,
                ['initiated', 'processing'],
                true
            ),
            422,
            'This offline payment is no longer awaiting verification.'
        );

        $paymentMethod = DB::table('offline_payment_methods')
            ->where(
                'id',
                $attemptRecord->payment_method_id
                    ?? ($metadata['offline_payment_method_id'] ?? null)
            )
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        abort_unless(
            (bool) $paymentMethod->receipt_upload_enabled,
            422,
            'Receipt upload is not enabled for this payment method.'
        );

        $data = $request->validate([
            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $path = $data['receipt']->store(
            'offline-payment-receipts',
            'public'
        );

        /*
         * Remove the buyer's previous proof if they replace it.
         */
        if (!empty($metadata['receipt_path'])) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete($metadata['receipt_path']);
        }

        $metadata['receipt_path'] = $path;
        $metadata['receipt_original_name'] =
            $data['receipt']->getClientOriginalName();
        $metadata['receipt_uploaded_at'] = now()->toDateTimeString();

        DB::table('payment_attempts')
            ->where('id', $attemptRecord->id)
            ->update([
                'status' => 'processing',
                'metadata' => json_encode($metadata),
                'updated_at' => now(),
            ]);

        if ($isWalletFunding) {
            $walletFundingId = $metadata['wallet_funding_id']
                ?? $payload['wallet_funding_id']
                ?? null;

            abort_unless($walletFundingId, 404);

            \App\Models\WalletFunding::query()
                ->where('id', (int) $walletFundingId)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'processing'])
                ->update([
                    'status' => 'processing',
                ]);
        }

        /*
         * ESUBIZ_OFFLINE_RECEIPT_AWAITING_VERIFICATION_V3
         *
         * Receipt submission is not payment completion.
         * Keep the buyer on the verification page and let them
         * choose where to continue next.
         */
        return redirect()
            ->route(
                'marketplace.developer.offline-payment',
                ['attempt' => $attemptRecord->id]
            )
            ->with(
                'offline_payment_success',
                'Payment receipt submitted successfully. Your payment is awaiting administrator verification.'
            );
    }


    protected function processUnifiedOfflineMarketplacePayment(
        Request $request,
        ?string $context = null
    ) {
        $context = $context ?: $request->input('deployment_type');

        abort_unless(
            in_array($context, ['saas', 'off_server'], true),
            422,
            'Invalid marketplace checkout context.'
        );

        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'payment_option' => ['required', 'string', 'max:255'],
        ]);

        [$mode, $methodId] = array_pad(
            explode(':', $data['payment_option'], 2),
            2,
            null
        );

        abort_unless($mode === 'offline' && $methodId, 422, 'Invalid offline payment method.');

        $order = DB::table('marketplace_orders')
            ->where('id', $data['order_id'])
            ->where('buyer_id', auth()->id())
            ->where('payment_status', 'pending')
            ->first();

        abort_unless($order, 404);

        $checkoutSession = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $order->id)
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        abort_unless($checkoutSession, 404);

        abort_unless(
            $checkoutSession->deployment_type === $context,
            422,
            'Marketplace checkout context mismatch.'
        );

        $offlineMethod = DB::table('offline_payment_methods')
            ->where('id', (int) $methodId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless(
            $offlineMethod,
            422,
            'Selected offline payment method is unavailable.'
        );

        /*
         * Calculate the amount actually payable through this
         * Offline gateway.
         *
         * Marketplace order value remains untouched.
         *
         * Order amount
         * -> conversion
         * -> markup
         * -> actual payment amount
         */
        try {
            $paymentCalculation = app(
                \App\Services\Core\PaymentConversionService::class
            )->calculate(
                $offlineMethod,
                strtoupper($order->currency ?: 'NGN'),
                (float) $order->amount
            );
        } catch (\Throwable $e) {
            abort(
                422,
                $e->getMessage()
            );
        }

        $gatewayAmount = (float) (
            $paymentCalculation['payment_amount']
                ?? $order->amount
        );

        $gatewayCurrency = strtoupper(
            $paymentCalculation['to_currency']
                ?? $order->currency
                ?? 'NGN'
        );

        $reference =
            'MKT-OFF-' . strtoupper(bin2hex(random_bytes(6)));

        $transactionId = DB::table('payment_transactions')->insertGetId([
            'workspace_id' => $order->workspace_id,
            'wallet_id' => null,
            'payment_provider_id' => null,
            'reference' => $reference,
            'amount' => $gatewayAmount,
            'currency' => $gatewayCurrency,
            'status' => 'pending',
            'payload' => json_encode([
                'marketplace_order_id' => $order->id,
                'marketplace_order_reference' => $order->reference,
                'deployment_type' => $context,
                'buyer_id' => auth()->id(),
                'payment_mode' => 'offline',
                'offline_payment_method_id' =>
                    $offlineMethod->id,

                'offline_payment_method' =>
                    $offlineMethod->slug ?? null,

                /*
                 * Preserve both sides of the calculation.
                 */
                'source_amount' =>
                    (float) $order->amount,

                'source_currency' =>
                    strtoupper(
                        $order->currency ?: 'NGN'
                    ),

                'gateway_amount' =>
                    $gatewayAmount,

                'gateway_currency' =>
                    $gatewayCurrency,

                'payment_conversion' =>
                    $paymentCalculation,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attemptId = DB::table('payment_attempts')->insertGetId([
            'payment_transaction_id' => $transactionId,
            'payment_provider_id' => null,
            'payment_method_id' => $offlineMethod->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'attempt_number' => 1,
            'gateway_reference' => $reference,
            'gateway_response' => null,
            'amount' => $gatewayAmount,
            'status' => 'initiated',
            'failure_code' => null,
            'failure_reason' => null,
            'metadata' => json_encode([
                'payment_mode' => 'offline',
                'marketplace_order_id' => $order->id,
                'marketplace_order_reference' => $order->reference,
                'deployment_type' => $context,
                'buyer_id' => auth()->id(),
                'product_type' => $checkoutSession->product_type ?? null,
                'product_id' => $checkoutSession->product_id ?? null,
                'website_id' => $context === 'saas'
                    ? ($checkoutSession->website_id ?? null)
                    : null,
                'offline_payment_method_id' =>
                    $offlineMethod->id,

                'source_amount' =>
                    (float) $order->amount,

                'source_currency' =>
                    strtoupper(
                        $order->currency ?: 'NGN'
                    ),

                'gateway_amount' =>
                    $gatewayAmount,

                'gateway_currency' =>
                    $gatewayCurrency,

                'payment_conversion' =>
                    $paymentCalculation,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('marketplace_checkout_sessions')
            ->where('id', $checkoutSession->id)
            ->update([
                'payment_transaction_id' => $transactionId,
                'payment_method_id' => $offlineMethod->id,
                'status' => 'pending_payment',
                'updated_at' => now(),
            ]);

        return redirect()->route('marketplace.developer.offline-payment', [
            'attempt' => $attemptId,
        ])->with(
            'success',
            'Offline payment selected. Complete the payment using the instructions below.'
        );
    }

    protected function processUnifiedWalletMarketplacePayment(
        Request $request,
        ?string $context = null
    ) {
        $context = $context ?: $request->input('deployment_type');

        abort_unless(
            in_array($context, ['saas', 'off_server'], true),
            422,
            'Invalid marketplace checkout context.'
        );

        $data = $request->validate([
            'order_id' => ['required', 'integer'],
        ]);

        return DB::transaction(function () use ($data, $context) {
            $order = DB::table('marketplace_orders')
                ->where('id', $data['order_id'])
                ->where('buyer_id', auth()->id())
                ->lockForUpdate()
                ->first();

            abort_unless($order, 404);

            if ($order->payment_status === 'paid') {
                return redirect()->route(
                    'marketplace.payment-status',
                    ['order' => $order->id]
                );
            }

            $checkoutSession = DB::table('marketplace_checkout_sessions')
                ->where('marketplace_order_id', $order->id)
                ->where('user_id', auth()->id())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            abort_unless($checkoutSession, 404);

            abort_unless(
                $checkoutSession->deployment_type === $context,
                422,
                'Marketplace checkout context mismatch.'
            );

            $wallet = DB::table('wallets')
                ->where('user_id', auth()->id())
                ->whereIn('type', ['customer', 'developer'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderByRaw("CASE WHEN type = 'customer' THEN 0 ELSE 1 END")
                ->lockForUpdate()
                ->first();

            abort_unless($wallet, 422, 'No active wallet is available.');

            $amount = (float) $order->amount;
            $walletBalance = (float) $wallet->available_balance;

            abort_unless(
                strtoupper($wallet->currency ?? 'NGN')
                    === strtoupper($order->currency ?? 'NGN'),
                422,
                'Wallet currency does not match the order currency.'
            );

            abort_unless(
                $walletBalance >= $amount,
                422,
                'Insufficient wallet balance.'
            );

            /*
             * Debit through the canonical WalletService so every wallet
             * Marketplace purchase creates a wallet_transactions ledger row.
             */
            $walletModel = \App\Models\Wallet::query()
                ->where('id', $wallet->id)
                ->firstOrFail();

            app(\App\Services\Core\WalletService::class)
                ->debitForPayment(
                    $walletModel,
                    $amount,
                    null,
                    'Marketplace purchase - ' . $order->reference
                );

            $transactionReference =
                'MKT-WAL-' . strtoupper(bin2hex(random_bytes(6)));

            $paymentTransactionId = DB::table('payment_transactions')
                ->insertGetId([
                    'workspace_id' => $order->workspace_id,
                    'wallet_id' => $wallet->id,
                    'payment_provider_id' => null,
                    'reference' => $transactionReference,
                    'amount' => $amount,
                    'currency' => strtoupper($order->currency ?: 'NGN'),
                    'status' => 'completed',
                    'payload' => json_encode([
                        'marketplace_order_id' => $order->id,
                        'marketplace_order_reference' => $order->reference,
                        'deployment_type' => $context,
                        'buyer_id' => auth()->id(),
                        'payment_mode' => 'wallet',
                        'wallet_id' => $wallet->id,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('marketplace_orders')
                ->where('id', $order->id)
                ->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            DB::table('marketplace_checkout_sessions')
                ->where('id', $checkoutSession->id)
                ->update([
                    'payment_transaction_id' => $paymentTransactionId,
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            app(
                \App\Services\Marketplace\MarketplaceFinancialRecorder::class
            )->record(
                (int) $order->id,
                (int) $paymentTransactionId
            );

            return redirect()->route(
                'marketplace.payment-status',
                ['order' => $order->id]
            );
        });
    }

    protected function processUnifiedGiftCardMarketplacePayment(
        Request $request,
        ?string $context = null
    ) {
        $context = $context ?: $request->input('deployment_type');

        abort_unless(
            in_array($context, ['saas', 'off_server'], true),
            422,
            'Invalid marketplace checkout context.'
        );

        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'gift_card_code' => ['required', 'string', 'max:100'],
        ]);

        return DB::transaction(function () use ($data, $context) {

            $order = DB::table('marketplace_orders')
                ->where('id', $data['order_id'])
                ->where('buyer_id', auth()->id())
                ->lockForUpdate()
                ->first();

            abort_unless($order, 404);

            if ($order->payment_status === 'paid') {
                return redirect()->route(
                    'marketplace.payment-status',
                    ['order' => $order->id]
                );
            }

            $checkoutSession = DB::table(
                    'marketplace_checkout_sessions'
                )
                ->where(
                    'marketplace_order_id',
                    $order->id
                )
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->latest('id')
                ->lockForUpdate()
                ->first();

            abort_unless($checkoutSession, 404);

            abort_unless(
                $checkoutSession->deployment_type === $context,
                422,
                'Marketplace checkout context mismatch.'
            );

            $giftCards = app(
                \App\Services\Core\GiftCardService::class
            );

            /*
             * Validate centrally first so checkout uses the exact same
             * enablement, expiry, usage-limit and checkout rules as every
             * other Esubiz Gift Card integration.
             */
            try {
                $card = $giftCards->validate(
                    $data['gift_card_code'],
                    (float) $order->amount,
                    'checkout'
                );
            } catch (\Throwable $e) {
                abort(422, $e->getMessage());
            }

            abort_unless(
                empty($card->issued_to_user_id)
                    || (int) $card->issued_to_user_id
                        === (int) auth()->id(),
                403,
                'This Gift Card belongs to another user.'
            );

            abort_unless(
                strtoupper($card->currency ?? 'NGN')
                    === strtoupper($order->currency ?? 'NGN'),
                422,
                'Gift Card currency does not match the order currency.'
            );

            /*
             * Split payment is not enabled yet. Therefore the Gift Card
             * must cover the complete marketplace order before redemption.
             */
            abort_unless(
                (float) $card->remaining_balance
                    >= (float) $order->amount,
                422,
                'Gift Card balance does not fully cover this order.'
            );

            try {
                $redemption = $giftCards->redeem(
                    $data['gift_card_code'],
                    (float) $order->amount,
                    (int) auth()->id(),
                    'marketplace_checkout',
                    'marketplace_order',
                    (int) $order->id,
                    [
                        'marketplace_order_reference'
                            => $order->reference,
                        'deployment_type'
                            => $context,
                        'product_type'
                            => $checkoutSession->product_type ?? null,
                        'product_id'
                            => $checkoutSession->product_id ?? null,
                        'website_id'
                            => $context === 'saas'
                                ? ($checkoutSession->website_id ?? null)
                                : null,
                    ]
                );
            } catch (\Throwable $e) {
                abort(422, $e->getMessage());
            }

            $transactionReference =
                'MKT-GFT-' . strtoupper(
                    bin2hex(random_bytes(6))
                );

            $paymentTransactionId = DB::table(
                    'payment_transactions'
                )
                ->insertGetId([
                    'workspace_id'
                        => $order->workspace_id,
                    'wallet_id'
                        => null,
                    'payment_provider_id'
                        => null,
                    'reference'
                        => $transactionReference,
                    'amount'
                        => (float) $order->amount,
                    'currency'
                        => strtoupper(
                            $order->currency ?: 'NGN'
                        ),
                    'status'
                        => 'completed',
                    'payload'
                        => json_encode([
                            'marketplace_order_id'
                                => $order->id,
                            'marketplace_order_reference'
                                => $order->reference,
                            'deployment_type'
                                => $context,
                            'buyer_id'
                                => auth()->id(),
                            'payment_mode'
                                => 'gift_card',
                            'gift_card_id'
                                => $redemption->gift_card_id,
                            'gift_card_transaction_id'
                                => $redemption->transaction_id,
                            'gift_card_reference'
                                => $redemption->reference,
                            'gift_card_redeemed_amount'
                                => $redemption->amount,
                            'gift_card_remaining_balance'
                                => $redemption->remaining_balance,
                        ]),
                    'created_at'
                        => now(),
                    'updated_at'
                        => now(),
                ]);

            DB::table('marketplace_orders')
                ->where('id', $order->id)
                ->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            DB::table('marketplace_checkout_sessions')
                ->where('id', $checkoutSession->id)
                ->update([
                    'payment_transaction_id'
                        => $paymentTransactionId,
                    'status'
                        => 'completed',
                    'updated_at'
                        => now(),
                ]);

            app(
                \App\Services\Marketplace\MarketplaceFinancialRecorder::class
            )->record(
                (int) $order->id,
                (int) $paymentTransactionId
            );

            return redirect()->route(
                'marketplace.payment-status',
                ['order' => $order->id]
            );
        });
    }

    protected function processUnifiedMarketplacePayment(
        Request $request,
        ?string $context = null
    ) {
        $context = $context ?: $request->input('deployment_type');

        abort_unless(
            in_array($context, ['saas', 'off_server'], true),
            422,
            'Invalid marketplace checkout context.'
        );

        if ($context === 'saas') {
            return $this->saasPayment($request);
        }

        return $this->developerCheckoutSubmit($request);
    }

    public function saasPayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'payment_option' => ['required', 'string', 'max:150'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);

        abort_unless(
            $quantity === 1,
            422,
            'SaaS checkout quantity must be one.'
        );

        $order = DB::table('marketplace_orders')
            ->where('id', $data['order_id'])
            ->where('buyer_id', auth()->id())
            ->where('payment_status', 'pending')
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        abort_unless($order, 404);

        [$paymentMode, $paymentIdentifier] = array_pad(
            explode(':', $data['payment_option'], 2),
            2,
            null
        );

        abort_unless(
            in_array($paymentMode, ['online', 'offline', 'wallet'], true),
            422,
            'Invalid payment method.'
        );

        abort_unless(
            $paymentIdentifier !== null || $paymentMode === 'wallet',
            422,
            'Payment method is not selected.'
        );

        /*
         * ONLINE PAYMENT
         *
         * Reuse the central Esubiz gateway manager. SaaS checkout does
         * not implement gateway-specific payment logic itself.
         */
        if ($paymentMode === 'online') {

            $paymentProvider = DB::table('payment_providers')
                ->where('slug', $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentProvider,
                422,
                'The selected payment gateway is unavailable.'
            );

            /*
             * Apply Online gateway conversion + markup.
             *
             * CoinGecko automatically uses the existing:
             * services.coingecko.demo_api_key configuration.
             *
             * No API key is exposed to the customer.
             */
            try {
                $paymentCalculation = app(
                    \App\Services\Core\PaymentConversionService::class
                )->calculate(
                    $paymentProvider,
                    strtoupper($order->currency ?: 'NGN'),
                    (float) $order->amount
                );
            } catch (\Throwable $e) {
                abort(
                    422,
                    $e->getMessage()
                );
            }

            $gatewayAmount = (float) (
                $paymentCalculation['payment_amount']
                    ?? $order->amount
            );

            $gatewayCurrency = strtoupper(
                $paymentCalculation['to_currency']
                    ?? $order->currency
                    ?? 'NGN'
            );

            $providerCredentials = $paymentProvider->credentials
                ? (json_decode($paymentProvider->credentials, true) ?: [])
                : [];

            $transactionReference = 'SAAS-TXN-' . strtoupper(
                \Illuminate\Support\Str::random(12)
            );

            $paymentTransactionId = DB::table('payment_transactions')
                ->insertGetId([
                    'workspace_id' => $order->workspace_id,
                    'wallet_id' => null,
                    'payment_provider_id' => $paymentProvider->id,
                    'reference' => $transactionReference,
                    'amount' => $gatewayAmount,
                    'currency' => $gatewayCurrency,
                    'status' => 'pending',
                    'payload' => json_encode([
                        'marketplace_order_id' => $order->id,
                        'marketplace_order_reference' => $order->reference,
                        'product_type' => $order->product_type ?? null,
                        'product_id' => $order->product_id ?? null,
                        'quantity' => 1,
                        'buyer_id' => auth()->id(),
                        'payment_mode' => 'online',
                        'payment_provider' =>
                            $paymentProvider->slug,

                        'payment_provider_id' =>
                            $paymentProvider->id,

                        'source_amount' =>
                            (float) $order->amount,

                        'source_currency' =>
                            strtoupper(
                                $order->currency ?: 'NGN'
                            ),

                        'gateway_amount' =>
                            $gatewayAmount,

                        'gateway_currency' =>
                            $gatewayCurrency,

                        'payment_conversion' =>
                            $paymentCalculation,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $attemptService = app(
                \App\Services\Payment\CorePaymentAttemptService::class
            );

            $attempt = $attemptService->create(
                (int) $paymentTransactionId,
                (int) $paymentProvider->id,
                null,
                $gatewayAmount,
                [
                    'marketplace_order_id' => $order->id,
                    'marketplace_order_reference' => $order->reference,
                    'product_type' => $order->product_type ?? null,
                    'product_id' => $order->product_id ?? null,
                    'quantity' => 1,
                    'buyer_id' => auth()->id(),
                    'payment_mode' => 'online',

                    'source_amount' =>
                        (float) $order->amount,

                    'source_currency' =>
                        strtoupper(
                            $order->currency ?: 'NGN'
                        ),

                    'gateway_amount' =>
                        $gatewayAmount,

                    'gateway_currency' =>
                        $gatewayCurrency,

                    'payment_conversion' =>
                        $paymentCalculation,
                ]
            );

            $gatewayManager = app(
                \App\Services\Payment\CorePaymentGatewayManager::class
            );

            $gateway = $gatewayManager->resolve(
                $paymentProvider->slug
            );

            $customer = DB::table('users')
                ->where('id', auth()->id())
                ->first();

            $result = $gateway->initialize(
                DB::table('payment_transactions')
                    ->where('id', $paymentTransactionId)
                    ->first(),
                $attempt,
                [
                    'payment_provider' => $paymentProvider->slug,
                    'payment_provider_id' => $paymentProvider->id,
                    'secret_key' => $providerCredentials['secret_key'] ?? null,
                    'public_key' => $providerCredentials['public_key'] ?? null,
                    'email' => $customer->email ?? null,
                    'customer_name' => $customer->name ?? null,
                    'callback_url' => route(
                        'marketplace.payment-status',
                        ['order' => $order->id]
                    ),
                    'return_url' => route(
                        'marketplace.payment-status',
                        ['order' => $order->id]
                    ),
                ]
            );

            $attemptService->update(
                (int) $attempt->id,
                'processing',
                $result['gateway_reference']
                    ?? $result['reference']
                    ?? null,
                $result
            );

            $authorizationUrl =
                $result['authorization_url']
                ?? $result['link']
                ?? null;

            abort_unless(
                $authorizationUrl,
                502,
                'Payment gateway did not return a checkout URL.'
            );

            return redirect()->away($authorizationUrl);
        }

        /*
         * Offline and wallet payment paths will use the same SaaS order
         * and transaction lifecycle. They are intentionally handled
         * separately from hosted online gateway initialization.
         */
        abort(422, 'The selected payment method is not yet connected.');

    }


    public function developerPayment(Request $request)
    {
        $data = $request->validate([
            'transaction_reference' => ['required', 'string'],
        ]);

        $transaction = DB::table('payment_transactions')
            ->where('reference', $data['transaction_reference'])
            ->where('status', 'pending')
            ->first();

        abort_unless($transaction, 404);

        $payload = $transaction->payload
            ? json_decode($transaction->payload, true)
            : [];

        $gatewaySlug = $payload['payment_provider'] ?? null;

        abort_unless($gatewaySlug, 422, 'Payment gateway is not configured.');

        $gatewayManager = app(
            \App\Services\Payment\CorePaymentGatewayManager::class
        );

        $gateway = $gatewayManager->resolve($gatewaySlug);

        $attemptService = app(
            \App\Services\Payment\CorePaymentAttemptService::class
        );

        $attemptMetadata = [
            'marketplace_order_id' => $payload['marketplace_order_id'] ?? null,
            'marketplace_order_reference' => $payload['marketplace_order_reference'] ?? null,
            'product_type' => $payload['product_type'] ?? null,
            'product_id' => $payload['product_id'] ?? null,
            'quantity' => $payload['quantity'] ?? null,
            'buyer_id' => $payload['buyer_id'] ?? auth()->id(),
            'payment_mode' => $payload['payment_mode'] ?? 'online',
        ];

        $attempt = $attemptService->create(
            (int) $transaction->id,
            (int) $transaction->payment_provider_id,
            (int) $transaction->payment_method_id,
            (float) $transaction->amount,
            $attemptMetadata
        );

        $providerCredentials = $transaction->payment_provider_id
            ? DB::table('payment_providers')
                ->where('id', $transaction->payment_provider_id)
                ->value('credentials')
            : null;

        $providerCredentials = $providerCredentials
            ? (json_decode($providerCredentials, true) ?: [])
            : [];

        $result = $gateway->initialize(
            $transaction,
            $attempt,
            [
                'payment_method' => $payload['payment_method'] ?? null,
                'payment_method_id' => $transaction->payment_method_id,
                'payment_provider' => $gatewaySlug,
                'secret_key' => $providerCredentials['secret_key'] ?? null,
                'public_key' => $providerCredentials['public_key'] ?? null,
                'return_url' => route('marketplace.payment-status', [
                    'order' => $transaction->marketplace_order_id,
                ]),
                'callback_url' => route('marketplace.payment-status', [
                    'order' => $transaction->marketplace_order_id,
                ]),
            ]
        );

        $attemptService->update(
            (int) $attempt->id,
            'processing',
            $result['reference'] ?? null,
            $result
        );

        return response()->json([
            'status' => 'initialized',
            'reference' => $transaction->reference,
            'gateway' => $gatewaySlug,
            'attempt' => $attempt->uuid,
            'payment' => $result,
        ]);
    }


    public function developerLibrary()
    {
        $orders = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_listings as listings',
                'listings.id',
                '=',
                'orders.marketplace_listing_id'
            )
            ->join(
                'marketplace_checkout_sessions as checkout_sessions',
                'checkout_sessions.marketplace_order_id',
                '=',
                'orders.id'
            )
            ->where('orders.buyer_id', auth()->id())
            ->where('checkout_sessions.deployment_type', 'off_server')
            ->where('orders.payment_status', 'paid')
            ->whereIn('orders.status', ['completed', 'fulfilled'])
            ->select(
                'orders.*',
                'listings.product_type',
                'listings.product_id',
                'listings.title as product_title',
                'listings.slug as product_slug'
            )
            ->latest('orders.updated_at')
            ->get();

        // ESUBIZ_DEVELOPER_BUILD_LIBRARY_V1
        $developerBuilds = \App\Models\DeveloperBuild::query()
            ->where('developer_id', auth()->id())
            ->where('status', 'success')
            ->where('payment_status', 'paid')
            ->whereNotNull('package_reference')
            ->latest('updated_at')
            ->get();

        return view('marketplace.developer-library', [
            'orders' => $orders,
            'developerBuilds' => $developerBuilds,
        ]);
    }


    // ESUBIZ_DEVELOPER_BUILD_PROTECTED_DOWNLOAD_V1
    public function developerBuildDownload(int $build)
    {
        $developerBuild = \App\Models\DeveloperBuild::query()
            ->where('id', $build)
            ->where('developer_id', auth()->id())
            ->where('status', 'success')
            ->where('payment_status', 'paid')
            ->firstOrFail();

        $entitled = DB::table('product_entitlements')
            ->where('user_id', auth()->id())
            ->where('product_type', 'developer_build')
            ->where('product_id', $developerBuild->id)
            ->where('status', 'active')
            ->where('fulfilment_type', 'license')
            ->whereNull('deleted_at')
            ->exists();

        abort_unless(
            $entitled,
            403,
            'This Developer Build is not entitled for download.'
        );

        $package = (string) $developerBuild->package_reference;

        abort_unless(
            $package !== '' && is_file($package),
            404,
            'Compiled package could not be found.'
        );

        $safeName = trim(
            preg_replace(
                '/[^A-Za-z0-9._-]+/',
                '-',
                (string) $developerBuild->project_name
            ),
            '-'
        );

        if ($safeName === '') {
            $safeName = 'esubiz-developer-build';
        }

        return response()->download(
            $package,
            'esubiz.zip',
            [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }


    public function developerPendingCheckouts()
    {
        /*
         * One pending row per Marketplace order.
         *
         * Do not join payment_transactions directly here because one order
         * can legitimately have multiple historical payment attempts and
         * that produces duplicate pending rows.
         */
        $checkouts = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_listings as listings',
                'listings.id',
                '=',
                'orders.marketplace_listing_id'
            )
            ->where('orders.buyer_id', auth()->id())
            ->where('orders.payment_status', 'pending')
            ->where('orders.status', 'pending')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('marketplace_checkout_sessions as sessions')
                    ->whereColumn(
                        'sessions.marketplace_order_id',
                        'orders.id'
                    )
                    ->where('sessions.user_id', auth()->id())
                    ->where('sessions.account_mode', 'developer')
                    ->where(
                        'sessions.deployment_type',
                        'off_server'
                    )
                    ->whereIn('sessions.status', [
                        'active',
                        'pending_payment',
                    ]);
            })
            ->select(
                'orders.*',
                'listings.product_type',
                'listings.product_id',
                'listings.title as listing_title',
                'listings.slug as listing_slug'
            )
            ->selectSub(function ($query) {
                $query->from('payment_transactions as transactions')
                    ->select('transactions.reference')
                    ->whereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(transactions.payload, '$.marketplace_order_id')) = CAST(orders.id AS CHAR)"
                    )
                    ->orderByDesc('transactions.id')
                    ->limit(1);
            }, 'transaction_reference')
            ->orderByDesc('orders.created_at')
            ->paginate(10);

        return view('marketplace.developer-pending-checkouts', [
            'checkouts' => $checkouts,
        ]);
    }


    public function continueDeveloperCheckout($order)
    {
        $orderRecord = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_checkout_sessions as sessions',
                'sessions.marketplace_order_id',
                '=',
                'orders.id'
            )
            ->where('orders.id', (int) $order)
            ->where('orders.buyer_id', auth()->id())
            ->where('sessions.user_id', auth()->id())
            ->where('sessions.account_mode', 'developer')
            ->where('sessions.deployment_type', 'off_server')
            ->select(
                'orders.id',
                'orders.payment_status',
                'orders.status'
            )
            ->orderByDesc('sessions.id')
            ->first();

        abort_unless($orderRecord, 404);

        /*
         * A paid/completed order can never be continued for payment.
         */
        if (
            $orderRecord->payment_status === 'paid'
            || in_array(
                $orderRecord->status,
                ['completed', 'fulfilled'],
                true
            )
        ) {
            return redirect()->route(
                'marketplace.payment-status',
                ['order' => $orderRecord->id]
            )->with(
                'status',
                'This order has already been paid.'
            );
        }

        abort_unless(
            $orderRecord->payment_status === 'pending'
                && $orderRecord->status === 'pending',
            422,
            'This checkout is no longer available for payment.'
        );

        /*
         * Reopen the existing shared Marketplace checkout.
         * Do NOT send it back through developerCheckoutSubmit(), because
         * that path creates another Marketplace order.
         */
        return redirect()->route(
            'marketplace.checkout',
            ['order' => $orderRecord->id]
        );
    }


    public function adminCheckoutSessions()
    {
        $sessions = DB::table('marketplace_checkout_sessions as sessions')
            ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
            ->leftJoin('marketplace_orders as orders', 'orders.id', '=', 'sessions.marketplace_order_id')
            ->leftJoin('payment_transactions as transactions', 'transactions.id', '=', 'sessions.payment_transaction_id')
            ->whereIn('sessions.status', [
                'active',
                'pending_payment',
            ])
            ->select(
                'sessions.*',
                'users.name as buyer_name',
                'users.email as buyer_email',
                'orders.reference as order_reference',
                'transactions.reference as transaction_reference'
            )
            ->latest('sessions.created_at')
            ->get();

        return view('admin.marketplace.checkout-sessions', [
            'sessions' => $sessions,
        ]);
    }


    public function adminDeleteCheckoutSession($session)
    {
        $checkout = DB::table('marketplace_checkout_sessions')
            ->where('id', $session)
            ->first();

        abort_unless($checkout, 404);

        if (in_array($checkout->status, ['completed'], true)) {
            abort(403, 'Completed checkout sessions cannot be deleted.');
        }

        $orderId = $checkout->marketplace_order_id;
        $transactionId = $checkout->payment_transaction_id;

        DB::transaction(function () use ($checkout, $orderId, $transactionId) {

            DB::table('marketplace_checkout_sessions')
                ->where('id', $checkout->id)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);

            if ($transactionId) {
                DB::table('payment_transactions')
                    ->where('id', $transactionId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => now(),
                    ]);
            }

            if ($orderId) {
                DB::table('marketplace_orders')
                    ->where('id', $orderId)
                    ->where('payment_status', 'pending')
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'payment_status' => 'failed',
                        'status' => 'cancelled',
                        'updated_at' => now(),
                    ]);
            }
        });

        return redirect()
            ->route('admin.marketplace.checkout-sessions')
            ->with('status', 'Checkout session cancelled successfully.');
    }

    public function addons()
    {
        $addons = DB::table('core_addons')
            ->where('is_active', true)
            ->where('saas_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $bundles = DB::table('core_addon_bundles')
            ->where('is_active', true)
            ->where('saas_available', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $bundleItems = DB::table('core_addon_bundle_items')
            ->join('core_addons', 'core_addon_bundle_items.addon_id', '=', 'core_addons.id')
            ->select(
                'core_addon_bundle_items.*',
                'core_addons.name',
                'core_addons.capabilities',
                'core_addons.default_allocation',
                'core_addons.allocation_unit'
            )
            ->get()
            ->groupBy('bundle_id');

        $capabilityAllocations = DB::table('core_addon_capability_allocations')
            ->join(
                'core_addons',
                'core_addon_capability_allocations.addon_id',
                '=',
                'core_addons.id'
            )
            ->select(
                'core_addon_capability_allocations.*',
                'core_addons.allocation_unit'
            )
            ->get()
            ->map(function ($allocation) {
                $key = (string) $allocation->capability_key;

                $allocation->display_name = ucwords(
                    str_replace(['_', '-'], ' ', preg_replace('/^crm_/', '', $key))
                );

                // The unit displayed in Marketplace comes exclusively
                // from the Admin-configured Add-on allocation unit.
                $allocation->display_unit =
                    $allocation->allocation_unit ?: null;

                $allocation->addon_allocation_unit =
                    $allocation->allocation_unit ?: null;

                return $allocation;
            })
            ->groupBy('addon_id');

        /*
         * Attach the actual capability allocations to every core add-on.
         * This is the authoritative source for quantities/unlimited status.
         */
        foreach ($addons as $addon) {
            $allocations = collect($capabilityAllocations->get($addon->id, []));

            if ($allocations->isEmpty()) {
                $capabilities = json_decode($addon->capabilities ?? '[]', true) ?: [];

                $allocations = collect($capabilities)->map(function ($key) {
                    return (object) [
                        'capability_key' => $key,
                        'allocation' => null,
                        'is_unlimited' => false,
                        'display_name' => ucwords(
                            str_replace(['_', '-'], ' ', preg_replace('/^crm_/', '', $key))
                        ),
                        'display_unit' => null,
                    ];
                });
            }

            $addon->capability_allocations = $allocations;
        }

        /*
         * Attach the same capability allocation data to every bundle item.
         * Bundle allocation controls remain authoritative for the included
         * add-on itself, while capability allocations describe what that
         * add-on actually provides.
         */
        foreach ($bundleItems as $items) {
            foreach ($items as $item) {
                $item->capability_allocations =
                    collect($capabilityAllocations->get($item->addon_id, []));
            }
        }

        return view('marketplace.index', [
            'addons' => $addons,
            'bundles' => $bundles,
            'bundleItems' => $bundleItems,
            'capabilityAllocations' => $capabilityAllocations,
        ]);
    }

    protected function catalogProductVisibleFor(object $product, string $deploymentType): bool
    {
        return match ($product->audience ?? 'both') {
            'saas' => $deploymentType === 'saas',
            'developer' => $deploymentType === 'off_server',
            'both' => true,
            default => false,
        };
    }

    protected function catalogProductsFor(string $deploymentType)
    {
        return DB::table('catalog_products')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($deploymentType) {
                if ($deploymentType === 'saas') {
                    $query->whereIn('audience', ['saas', 'both']);
                } else {
                    $query->whereIn('audience', ['developer', 'both']);
                }
            })
            ->orderBy('name')
            ->get();
    }

    protected function resolveMarketplaceListing(
        string $productType,
        int $productId
    ): ?\App\Models\MarketplaceListing {
        /*
         * Generic marketplace resolver.
         *
         * The checkout must remain product-agnostic. Normalize the
         * human-facing product type to the canonical marketplace type,
         * then resolve the published marketplace listing.
         */
        $canonicalType = match ($productType) {
            'addon',
            'core_addon',
            'core-addon' => 'core_addon',

            'bundle',
            'core_bundle',
            'core-bundle' => 'core_bundle',

            default => $productType,
        };

        $listing = \App\Models\MarketplaceListing::query()
            ->where('product_type', $canonicalType)
            ->where('product_id', $productId)
            ->where('status', 'published')
            ->first();

        /*
         * Fallback for marketplace products whose checkout already sends
         * the canonical product ID/type. This keeps direct addon checkout
         * independent of the UI's product-type naming.
         */
        if (!$listing && $canonicalType !== $productType) {
            $listing = \App\Models\MarketplaceListing::query()
                ->where('product_type', $productType)
                ->where('product_id', $productId)
                ->where('status', 'published')
                ->first();
        }

        return $listing;
    }

    public function developerAddons()
    {
        $addons = DB::table('core_addons')
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $capabilityAllocations = DB::table('core_addon_capability_allocations')
            ->whereIn('addon_id', $addons->pluck('id'))
            ->get()
            ->groupBy('addon_id');

        foreach ($addons as $addon) {
            $addon->capability_allocations =
                collect($capabilityAllocations->get($addon->id, []));
        }

        $bundles = DB::table('core_addon_bundles')
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $bundleItems = DB::table('core_addon_bundle_items as items')
            ->join('core_addons as addons', 'addons.id', '=', 'items.addon_id')
            ->whereIn('items.bundle_id', $bundles->pluck('id'))
            ->where('addons.is_active', true)
            ->whereNull('addons.deleted_at')
            ->select(
                'items.bundle_id',
                'addons.id as addon_id',
                'addons.name',
                'addons.allocation_unit',
                'items.allocation',
                'items.is_unlimited'
            )
            ->orderBy('addons.name')
            ->get()
            ->groupBy('bundle_id');

        /*
         * Restore the detailed bundle contents used by the Developer
         * marketplace. Each bundle item carries the underlying add-on
         * capability allocations so its included features/quantities
         * can be rendered exactly like standalone add-ons.
         */
        $bundleAddonIds = $bundleItems
            ->flatten()
            ->pluck('addon_id')
            ->filter()
            ->unique()
            ->values();

        $bundleCapabilityAllocations = DB::table(
                'core_addon_capability_allocations'
            )
            ->whereIn('addon_id', $bundleAddonIds)
            ->orderBy('addon_id')
            ->orderBy('id')
            ->get()
            ->groupBy('addon_id');

        foreach ($bundleItems as $items) {
            foreach ($items as $item) {
                $item->capability_allocations = collect(
                    $bundleCapabilityAllocations->get(
                        $item->addon_id,
                        []
                    )
                );
            }
        }

        return view('marketplace.developer-addons', [
            'addons' => $addons,
            'bundles' => $bundles,
            'bundleItems' => $bundleItems,
        ]);
    }

    /**
     * ESUBIZ_SAAS_MARKETPLACE_CHECKOUT_HANDOFF
     *
     * Secure bridge from a SaaS tenant website into the
     * existing Central Esubiz Marketplace checkout.
     *
     * This does not create another checkout implementation.
     * After validating the signed handoff, the request is
     * normalized and passed directly into checkout().
     */
    public function saasCheckoutHandoff(
        Request $request
    ) {
        abort_unless(
            $request->hasValidRelativeSignature(),
            403,
            'This marketplace checkout link is invalid or has expired.'
        );

        /*
         * CHECKPOINT 5 — TRUSTED SAAS BUYER HANDOFF
         *
         * The SaaS user may have authenticated only inside the tenant
         * website and therefore may not already have a Central Esubiz
         * browser session.
         *
         * The relative signature proves that this checkout request was
         * issued by the trusted Esubiz SaaS application. The browser
         * cannot alter website_id, product, return URL or the other
         * signed parameters without invalidating that signature.
         */
        $website =
            \App\Models\Website::query()
                ->where(
                    'id',
                    (int) $request->query(
                        'website_id'
                    )
                )
                ->firstOrFail();


        abort_unless(
            $website->isSaas()
                && $website->isRegistryActive(),
            403,
            'This SaaS website is not active in the Central Esubiz registry.'
        );


        /*
         * If a Central Esubiz session already exists, it must belong
         * to the website owner. We never silently switch an already
         * authenticated Central account to somebody else.
         */
        if (auth()->check()) {

            abort_unless(
                (int) auth()->id() ===
                    (int) $website->owner_id,
                403,
                'The current Esubiz account does not own this website.'
            );

            $buyerIdentitySource =
                'existing_central_session';

            $walletAllowed =
                true;

        } else {

            /*
             * No Central session exists.
             *
             * Establish the Central buyer from the authoritative
             * website registry. This allows a user who entered from
             * their authenticated SaaS admin to use the same Central
             * Marketplace checkout without manually signing in again.
             *
             * This is only reached AFTER successful signed-handoff
             * validation above.
             */
            abort_unless(
                !empty($website->owner_id),
                403,
                'This SaaS website does not have a Central owner.'
            );

            \Illuminate\Support\Facades\Auth::loginUsingId(
                (int) $website->owner_id
            );

            abort_unless(
                auth()->check()
                    && (int) auth()->id() ===
                        (int) $website->owner_id,
                401,
                'Unable to establish the Marketplace buyer session.'
            );

            $buyerIdentitySource =
                'signed_saas_handoff';

            /*
             * Do NOT automatically expose Central wallet funds merely
             * because a SaaS-local login initiated checkout.
             *
             * Card/gateway checkout can proceed. Wallet authorization
             * will require explicit Central-session authority.
             */
            $walletAllowed =
                false;
        }


        $checkoutContext =
            app(
                \App\Services\Marketplace\MarketplaceCheckoutContextService::class
            )->saas(
                $website,
                (int) auth()->id(),
                (string) $request->query(
                    'return_url',
                    ''
                ),
                (string) $request->query(
                    'return_area',
                    ''
                )
            );


        $checkoutContext['identity_source'] =
            $buyerIdentitySource;

        $checkoutContext['wallet_allowed'] =
            $walletAllowed;


        $deploymentType =
            strtolower(
                trim(
                    (string) (
                        $website->deployment_type
                        ?? 'saas'
                    )
                )
            );


        abort_unless(
            $deploymentType === ''
            || $deploymentType === 'saas',
            422,
            'This checkout handoff is only available to SaaS websites.'
        );


        /*
         * Convert the trusted signed GET handoff into the same
         * request contract consumed by the existing checkout().
         */
        $request->merge([
            'product_type' =>
                (string) $request->query(
                    'product_type'
                ),

            'product_id' =>
                (int) $request->query(
                    'product_id'
                ),

            'deployment_type' =>
                'saas',

            'website_id' =>
                (int) $website->id,

            'checkout_origin' =>
                $checkoutContext['checkout_origin'],

            'wallet_allowed' =>
                $checkoutContext['wallet_allowed']
                    ? '1'
                    : '0',

            'return_area' =>
                (string) $request->query(
                    'return_area',
                    ''
                ),

            'return_url' =>
                (string) $request->query(
                    'return_url',
                    ''
                ),
        ]);


        return $this->checkout(
            $request
        );
    }


    public function checkout(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'max:100'],
            'product_id' => ['required', 'integer'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'website_id' => ['nullable', 'integer'],

            /*
             * Unified marketplace entry context.
             *
             * central_account:
             *   Esubiz User / Developer marketplace
             *
             * saas_website:
             *   purchase initiated from a SaaS tenant admin
             *
             * off_server_website is deliberately not accepted
             * here yet; it will enter through a signed link.
             */
            'checkout_origin' => [
                'nullable',
                'in:central_account,saas_website,off_server_website',
            ],

            /*
             * Trusted checkout-entry services may explicitly
             * restrict payment methods.
             *
             * Examples:
             *
             * Central authenticated buyer:
             *     wallet_allowed = true
             *
             * SaaS local-login handoff:
             *     wallet_allowed = false
             *
             * Off-server Core handoff:
             *     wallet_allowed = false
             */
            'wallet_allowed' => [
                'nullable',
                'boolean',
            ],

            'return_area' => [
                'nullable',
                'string',
                'max:100',
            ],

            'return_url' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $website = DB::table('websites')
            ->where('id', $data['website_id'])
            ->when(
                session('account_mode', 'user') === 'developer',
                fn ($query) => $query->where('developer_id', auth()->id()),
                fn ($query) => $query->where('owner_id', auth()->id())
            )
            ->first();

        abort_unless($website, 403);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE CHECKOUT ORIGIN
        |--------------------------------------------------------------------------
        */

        $checkoutOrigin =
            $data['checkout_origin']
            ?? 'central_account';


        /*
         * A SaaS website-origin checkout must target a SaaS website.
         * The browser cannot use this flag to turn an off-server
         * purchase into a trusted SaaS-origin transaction.
         */
        if (
            $checkoutOrigin === 'saas_website'
            && $data['deployment_type'] !== 'saas'
        ) {
            abort(
                422,
                'SaaS website checkout can only use the SaaS marketplace context.'
            );
        }


        /*
         * --------------------------------------------------------
         * UNIFIED MARKETPLACE WALLET AUTHORITY
         * --------------------------------------------------------
         *
         * Wallet permission is determined by the trusted checkout
         * entry context, never merely by deployment type.
         *
         * Central authenticated checkout:
         *     normally true
         *
         * SaaS with genuine Central session:
         *     true
         *
         * SaaS local-login signed handoff:
         *     false
         *
         * Off-server Core checkout:
         *     always false here
         *
         * Off-server may later use Central Wallet only through an
         * explicitly authenticated Central-account checkout flow,
         * never through the external website bearer identity.
         */
        $walletAllowed =
            array_key_exists(
                'wallet_allowed',
                $data
            )
                ? (bool) $data['wallet_allowed']
                : true;


        if (
            $checkoutOrigin
            === 'off_server_website'
        ) {
            $walletAllowed =
                false;
        }


        /*
         * Logical destination after fulfilment.
         */
        $returnArea =
            trim(
                (string) (
                    $data['return_area']
                    ?? ''
                )
            );


        if ($returnArea === '') {

            $returnArea =
                match (
                    $data['product_type']
                ) {
                    'addon',
                    'core_addon',
                    'bundle',
                    'core_bundle'
                        => 'addons',

                    'theme'
                        => 'themes',

                    'module'
                        => 'modules',

                    'ai_credits',
                    'credit',
                    'credit_package'
                        => 'credits',

                    'sms_credits'
                        => 'sms',

                    'email_credits'
                        => 'email',

                    'whatsapp_credits'
                        => 'whatsapp',

                    default
                        => 'marketplace',
                };
        }


        /*
         * SaaS website return URLs may only point back into the
         * same Esubiz-hosted website.
         *
         * For central-account purchases, arbitrary return_url is
         * ignored and the normal central success destination applies.
         */
        $returnUrl =
            null;


        if (
            $checkoutOrigin === 'saas_website'
            && !empty(
                $data['return_url']
            )
        ) {

            $candidateUrl =
                trim(
                    (string)
                    $data['return_url']
                );


            $candidateHost =
                parse_url(
                    $candidateUrl,
                    PHP_URL_HOST
                );


            $expectedHost =
                $website->subdomain
                    ? $website->subdomain
                        . '.esubiz.com'
                    : null;


            if (
                $candidateHost
                && $expectedHost
                && strtolower($candidateHost)
                    === strtolower($expectedHost)
            ) {
                $returnUrl =
                    $candidateUrl;
            }
        }


        $listing = $this->resolveMarketplaceListing(
            $data['product_type'],
            (int) $data['product_id']
        );

        abort_unless($listing, 404);

        $productResolver = app(\App\Services\Marketplace\MarketplaceProductResolver::class);

        /*
         * All marketplace products now resolve through the same registry.
         * Core add-ons/bundles use their deployment flags while catalog
         * products use their SaaS/developer/both audience configuration.
         */
        $product = $productResolver->resolve(
            $data['product_type'],
            (int) $data['product_id']
        );

        abort_unless($product, 404);

        $available = $productResolver->available(
            $product,
            $data['deployment_type']
        );

        abort_unless($available, 422);

        $price = $productResolver->price(
            $product,
            $data['deployment_type']
        );

        $currency = $productResolver->currency(
            $product,
            $data['deployment_type']
        );

        abort_unless($price !== null && (float) $price >= 0, 422);

        /*
         * The marketplace product resolver is the single source of truth
         * for SaaS pricing. Keep the checkout amount aligned with it.
         */
        $amount = (float) $price;

        $orderReference = 'MKT-' . strtoupper(bin2hex(random_bytes(6)));

        $checkoutSessionId = DB::table('marketplace_checkout_sessions')->insertGetId([
            'user_id' => auth()->id(),
            'account_mode' => session('account_mode', 'user'),

            'checkout_origin' =>
                $checkoutOrigin,

            'return_url' =>
                $returnUrl,

            'return_area' =>
                $returnArea,

            'wallet_allowed' =>
                $walletAllowed,

            'origin_token_id' =>
                null,

            'product_type' => $data['product_type'],
            'product_id' => (int) $data['product_id'],
            'deployment_type' => $data['deployment_type'],
            'website_id' => $website->id,
            'workspace_id' => $website->workspace_id,
            'quantity' => 1,
            'unit_price' => $amount,
            'total_amount' => $amount,
            'currency' => $currency,
            'is_commissionable' => true,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('marketplace_orders')->insertGetId([
            'marketplace_listing_id' => $listing->id,
            'vendor_id' => $listing->vendor_id,
            'workspace_id' => $website->workspace_id,
            'buyer_id' => auth()->id(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference' => $orderReference,
            'amount' => $price,
            'commission_amount' => 0,
            'vendor_amount' => $price,
            'currency' => strtoupper($currency ?: 'NGN'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('marketplace_checkout_sessions')
            ->where('id', $checkoutSessionId)
            ->update([
                'marketplace_order_id' => $orderId,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('marketplace.checkout', ['order' => $orderId])
            ->with('success', 'Marketplace order created. Payment is still pending.');
    }

    public function paymentStatus(int $order)
    {
        $record = DB::table('marketplace_orders')
            ->where('id', $order)
            ->where('buyer_id', auth()->id())
            ->first();

        abort_unless($record, 404);

        /*
         * Synchronize the originating marketplace checkout session from
         * the canonical marketplace payment state.
         *
         * This is gateway-agnostic. Paystack, Flutterwave, offline,
         * wallet and future payment methods all converge here.
         */
        if ($record->payment_status === 'paid') {
            $paymentTransaction = DB::table('payment_transactions')
                ->where('payload', 'like', '%"marketplace_order_id":' . $record->id . '%')
                ->latest('id')
                ->first();

            if ($paymentTransaction) {
                DB::table('marketplace_checkout_sessions')
                    ->where('payment_transaction_id', $paymentTransaction->id)
                    ->where('status', 'pending_payment')
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
            }
        }

        /*
         * Resolve the payment transaction belonging to this marketplace
         * order. The transaction payload is intentionally product-agnostic.
         */
        $transaction = DB::table('payment_transactions')
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(payload, '$.marketplace_order_id')) = ?",
                [(string) $record->id]
            )
            ->latest('id')
            ->first();

        /*
         * ONLINE:
         * Verify through the central gateway interface. This applies to
         * every registered online gateway without gateway-specific logic.
         */
        if (
            $transaction &&
            $transaction->status === 'pending' &&
            $transaction->payment_provider_id
        ) {
            $payload = $transaction->payload
                ? (json_decode($transaction->payload, true) ?: [])
                : [];

            $paymentMode = $payload['payment_mode'] ?? 'online';

            if ($paymentMode === 'online') {
                $provider = DB::table('payment_providers')
                    ->where('id', $transaction->payment_provider_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($provider) {
                    $gatewayManager = app(
                        \App\Services\Payment\CorePaymentGatewayManager::class
                    );

                    $gateway = $gatewayManager->resolve($provider->slug);

                    $attempt = DB::table('payment_attempts')
                        ->where('payment_transaction_id', $transaction->id)
                        ->whereIn('status', ['initiated', 'processing'])
                        ->latest('id')
                        ->first();

                    if ($attempt) {
                        $credentials = $provider->credentials
                            ? (json_decode($provider->credentials, true) ?: [])
                            : [];

                        $verification = $gateway->verify(
                            $transaction,
                            $attempt,
                            [
                                'payment_provider' => $provider->slug,
                                'secret_key' => $credentials['secret_key'] ?? null,
                                'public_key' => $credentials['public_key'] ?? null,
                            ]
                        );

                        $verificationStatus = $verification['status'] ?? 'failed';

                        if ($verificationStatus === 'successful') {
                            app(
                                \App\Services\Payment\CorePaymentAttemptService::class
                            )->update(
                                (int) $attempt->id,
                                'successful',
                                $verification['gateway_reference']
                                    ?? $attempt->gateway_reference,
                                $verification
                            );

                            DB::table('payment_transactions')
                                ->where('id', $transaction->id)
                                ->update([
                                    'status' => 'successful',
                                    'updated_at' => now(),
                                ]);

                            DB::table('marketplace_orders')
                                ->where('id', $record->id)
                                ->update([
                                    'payment_status' => 'paid',
                                    'status' => 'processing',
                                    'updated_at' => now(),
                                ]);

                            $record->payment_status = 'paid';
                            $record->status = 'processing';

                            /*
                             * Record the successful marketplace payment as
                             * Esubiz platform revenue before fulfilment.
                             *
                             * The platform sale service feeds the existing
                             * CoreTransactionService financial records so
                             * the amount appears in Admin revenue, income
                             * and ledger reporting.
                             */
                            $listing = DB::table('marketplace_listings')
                                ->where('id', $record->marketplace_listing_id)
                                ->first();

                            if ($listing) {
                                app(
                                    \App\Services\Core\EsubizPlatformSaleService::class
                                )->record(
                                    $record->workspace_id ?? null,
                                    match ($listing->product_type) {
                                        'core_addon', 'addon' => 'addons',
                                        'core_bundle', 'bundle' => 'addons',
                                        'theme' => 'themes',
                                        'module' => 'modules',
                                        'subscription' => 'subscriptions',
                                        'hybrid' => 'hybrid',
                                        'ai_credits' => 'ai_credits',
                                        'sms_credits' => 'sms_credits',
                                        'email_credits' => 'email_credits',
                                        'whatsapp_credits' => 'whatsapp_credits',
                                        default => 'marketplace',
                                    },
                                    $listing->product_type,
                                    (int) $listing->product_id,
                                    $listing->title,
                                    (float) $record->amount,
                                    $record->currency ?: 'NGN',
                                    auth()->id(),
                                    $record->website_id ?? null,
                                    'purchase',
                                    [
                                        'marketplace_order_id' => $record->id,
                                        'marketplace_order_reference' => $record->reference,
                                        'payment_transaction_id' => $transaction->id ?? null,
                                        'deployment_type' => $record->deployment_type ?? 'saas',
                                        'developer_id' => $listing->developer_id ?? null,
                                        'financial_account_developer_id' => $listing->developer_id ?? null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        /*
         * FULFILMENT:
         * Once payment is confirmed, use the existing generic marketplace
         * fulfilment manager. Do not hard-code gateway behaviour here.
         */
        if (
            $record->payment_status === 'paid' &&
            $record->status !== 'fulfilled'
        ) {
            // ESUBIZ_DEVELOPER_BUILD_PAYMENT_FULFILMENT_V1
            //
            // Developer Builder orders are Esubiz-owned compiled products.
            // They intentionally have no Marketplace listing/vendor, so
            // fulfil them through the existing WebsiteCompilerService.
            if ($record->developer_build_id) {
                $developerBuild = \App\Models\DeveloperBuild::query()
                    ->where('id', $record->developer_build_id)
                    ->where('developer_id', auth()->id())
                    ->first();

                abort_unless($developerBuild, 404);

                $checkoutSession = DB::table('marketplace_checkout_sessions')
                    ->where('marketplace_order_id', $record->id)
                    ->where('user_id', auth()->id())
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                abort_unless($checkoutSession, 404);

                abort_unless(
                    $checkoutSession->product_type === 'developer_build' &&
                    (int) $checkoutSession->product_id === (int) $developerBuild->id &&
                    $checkoutSession->deployment_type === 'off_server',
                    422,
                    'Invalid Developer Build checkout context.'
                );

                /*
                 * ESUBIZ_DEVELOPER_BUILD_PAYMENT_RELEASE_V1
                 *
                 * Developer Builds must already be successfully compiled
                 * before payment. Payment releases that existing package;
                 * it must never trigger recompilation.
                 */
                abort_unless(
                    $developerBuild->status === 'success' &&
                    !empty($developerBuild->package_reference),
                    422,
                    'Developer Build must be successfully compiled before payment.'
                );

                /*
                 * ESUBIZ_DEVELOPER_BUILD_PRIMARY_ENTITLEMENT_V1
                 *
                 * The compiled Developer Build is one off-server licensed
                 * distribution. Its primary build licence/package is the
                 * canonical Developer Library entitlement.
                 *
                 * Keep this idempotent so gateway returns, refreshes and
                 * repeated payment-status checks cannot duplicate ownership.
                 */
                $buildEntitlement = DB::table('product_entitlements')
                    ->where('user_id', auth()->id())
                    ->where('product_type', 'developer_build')
                    ->where('product_id', $developerBuild->id)
                    ->first();

                $buildEntitlementMetadata = [
                    'item_type' => 'developer_build',
                    'source_module' => 'developer_builder',
                    'deployment_type' => 'off_server',
                    'developer_build_id' => $developerBuild->id,
                    'build_id' => $developerBuild->build_id,
                    'package_reference' =>
                        $developerBuild->package_reference,
                    'license_registration_id' =>
                        $developerBuild->license_registration_id,
                    'license_key' =>
                        $developerBuild->license_key,
                    'marketplace_order_id' => $record->id,
                    'marketplace_order_reference' =>
                        $record->reference,
                    'currency' =>
                        strtoupper($record->currency ?: 'NGN'),
                    'amount' => (float) $record->amount,
                ];

                if ($buildEntitlement) {
                    DB::table('product_entitlements')
                        ->where('id', $buildEntitlement->id)
                        ->update([
                            'product_name' =>
                                $developerBuild->project_name,
                            'status' => 'active',
                            'fulfilment_type' => 'license',
                            'metadata' => json_encode(
                                $buildEntitlementMetadata
                            ),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('product_entitlements')->insert([
                        'user_id' => auth()->id(),
                        'website_id' => null,
                        'workspace_id' => null,
                        'product_type' => 'developer_build',
                        'product_id' => $developerBuild->id,
                        'product_name' =>
                            $developerBuild->project_name,
                        'status' => 'active',
                        'fulfilment_type' => 'license',
                        'starts_at' => now(),
                        'metadata' => json_encode(
                            $buildEntitlementMetadata
                        ),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($developerBuild->payment_status !== 'paid') {
                    $developerBuild->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                DB::table('marketplace_orders')
                    ->where('id', $record->id)
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);

                DB::table('marketplace_checkout_sessions')
                    ->where('id', $checkoutSession->id)
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);

                $record->status = 'completed';
                $record->deployment_type = 'off_server';
                $record->website_id = null;

            } else {
                $listing = DB::table('marketplace_listings')
                    ->where('id', $record->marketplace_listing_id)
                    ->first();

                if ($listing) {

                /*
                 * The authoritative target website/deployment context
                 * lives on marketplace_checkout_sessions, not directly
                 * on marketplace_orders.
                 *
                 * This is shared by:
                 *
                 * - SaaS website checkout
                 * - Off-server website checkout link
                 * - Central Esubiz account checkout
                 *
                 * Fulfilment therefore resolves the purchase target from
                 * the originating checkout session before dispatching to
                 * the product-specific fulfilment handler.
                 */
                $checkoutSession =
                    DB::table(
                        'marketplace_checkout_sessions'
                    )
                        ->where(
                            'marketplace_order_id',
                            $record->id
                        )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->latest('id')
                        ->first();


                $deploymentType =
                    $checkoutSession->deployment_type
                    ?? $record->deployment_type
                    ?? (
                        str_starts_with(
                            (string) $record->reference,
                            'DEV-'
                        )
                            ? 'off_server'
                            : 'saas'
                    );


                $orderForFulfilment =
                    (object) array_merge(
                        (array) $record,
                        [
                            'deployment_type' =>
                                $deploymentType,

                            'website_id' =>
                                $checkoutSession->website_id
                                ?? $record->website_id
                                ?? null,

                            'workspace_id' =>
                                $checkoutSession->workspace_id
                                ?? $record->workspace_id
                                ?? null,

                            'checkout_origin' =>
                                $checkoutSession->checkout_origin
                                ?? null,

                            'return_url' =>
                                $checkoutSession->return_url
                                ?? null,

                            'return_area' =>
                                $checkoutSession->return_area
                                ?? null,

                            'wallet_allowed' =>
                                isset(
                                    $checkoutSession->wallet_allowed
                                )
                                    ? (bool) $checkoutSession->wallet_allowed
                                    : true,
                        ]
                    );


                app(
                    \App\Services\Marketplace\MarketplaceFulfilmentManager::class
                )->fulfil(
                    $orderForFulfilment,
                    $listing
                );


                DB::table(
                    'marketplace_orders'
                )
                    ->where(
                        'id',
                        $record->id
                    )
                    ->update([
                        'status' =>
                            'completed',

                        'updated_at' =>
                            now(),
                    ]);


                if ($checkoutSession) {
                    DB::table(
                        'marketplace_checkout_sessions'
                    )
                        ->where(
                            'id',
                            $checkoutSession->id
                        )
                        ->update([
                            'status' =>
                                'completed',

                            'updated_at' =>
                                now(),
                        ]);
                }


                $record->status =
                    'completed';

                $record->deployment_type =
                    $deploymentType;

                $record->website_id =
                    $orderForFulfilment
                        ->website_id;
                }
            }
        }

        /*
         * The payment-success page is presentation only.
         * Payment verification and fulfilment have already completed above.
         *
         * Determine the destination from the originating transaction
         * context so Developer and SaaS purchases are not treated alike.
         */
        $successPayload = $transaction?->payload
            ? (json_decode($transaction->payload, true) ?: [])
            : [];

        /*
         * Resolve the authoritative checkout origin again for the
         * presentation/return step.
         *
         * SaaS and off-server purchases must return to the exact
         * product page that started checkout:
         *
         * AI Credits -> AI page
         * SMS Credits -> SMS page
         * Add-on -> Add-on page
         * Theme -> Theme page
         * Module -> Module page
         * etc.
         */
        $returnCheckoutSession =
            DB::table(
                'marketplace_checkout_sessions'
            )
                ->where(
                    'marketplace_order_id',
                    $record->id
                )
                ->whereNull(
                    'deleted_at'
                )
                ->latest('id')
                ->first();


        $deploymentType =
            $returnCheckoutSession->deployment_type
            ?? $successPayload['deployment_type']
            ?? ($record->deployment_type ?? null)
            ?? (
                str_starts_with(
                    (string) $record->reference,
                    'DEV-'
                )
                    ? 'off_server'
                    : 'saas'
            );


        $checkoutOrigin =
            $returnCheckoutSession->checkout_origin
            ?? $successPayload['checkout_origin']
            ?? null;


        $originReturnUrl =
            trim(
                (string) (
                    $returnCheckoutSession->return_url
                    ?? $successPayload['return_url']
                    ?? ''
                )
            );


        /*
         * Only accept normal HTTP(S) return URLs.
         *
         * SaaS return URLs came through the signed Central handoff.
         * Off-server return URLs were validated against the registered
         * API application/domain before the checkout link was issued.
         */
        $validOriginReturnUrl =
            $originReturnUrl !== ''
            && filter_var(
                $originReturnUrl,
                FILTER_VALIDATE_URL
            )
            && in_array(
                strtolower(
                    (string) parse_url(
                        $originReturnUrl,
                        PHP_URL_SCHEME
                    )
                ),
                [
                    'http',
                    'https',
                ],
                true
            );


        /*
         * ========================================================
         * CHECKPOINT 6 - UNIFIED PAYMENT RESULT DESTINATION
         * ========================================================
         *
         * WEBSITE ORIGIN
         * --------------------------------------------------------
         * SaaS / Off-server:
         *
         * success, failed or pending
         *      -> payment popup
         *      -> Close
         *      -> exact originating website page
         *
         *
         * CENTRAL USER
         * --------------------------------------------------------
         * paid
         *      -> payment popup
         *      -> My Websites | Dashboard
         *
         * failed/pending
         *      -> payment popup
         *      -> Close
         *      -> Pending Checkout
         *
         *
         * CENTRAL DEVELOPER
         * --------------------------------------------------------
         * paid
         *      -> payment popup
         *      -> Close
         *      -> Developer Library
         *
         * failed/pending
         *      -> payment popup
         *      -> Close
         *      -> Pending Checkout
         */


        /*
         * Defaults are always defined so both paid and unpaid
         * presentation branches receive a complete view contract.
         */
        $showCentralUserActions =
            false;


        $centralDashboardUrl =
            url(
                '/dashboard'
            );


        $centralMyWebsitesUrl =
            url(
                '/websites'
            );


        $centralAccountMode =
            strtolower(
                trim(
                    (string) (
                        $returnCheckoutSession->account_mode
                        ?? $successPayload['account_mode']
                        ?? session(
                            'account_mode',
                            'user'
                        )
                    )
                )
            );


        $paymentIsPaid =
            strtolower(
                trim(
                    (string) (
                        $record->payment_status
                        ?? 'pending'
                    )
                )
            )
            === 'paid';


        /*
         * --------------------------------------------------------
         * WEBSITE-ORIGIN CHECKOUT
         * --------------------------------------------------------
         *
         * Payment status does not change navigation.
         *
         * The buyer always returns to the exact SaaS/off-server
         * page that initiated checkout.
         */
        if (
            in_array(
                $checkoutOrigin,
                [
                    'saas_website',
                    'off_server_website',
                ],
                true
            )
            && $validOriginReturnUrl
        ) {

            $successDestination =
                $originReturnUrl;


            $showCentralUserActions =
                false;


        /*
         * --------------------------------------------------------
         * CENTRAL FAILED / PENDING
         * --------------------------------------------------------
         *
         * Both User and Developer return to Pending Checkout so
         * the existing order can be retried rather than creating
         * another accidental purchase/payment.
         */
        } elseif (!$paymentIsPaid) {

            $successDestination =
                route(
                    'marketplace.checkout.index'
                );


            $showCentralUserActions =
                false;


        /*
         * --------------------------------------------------------
         * CENTRAL DEVELOPER SUCCESS
         * --------------------------------------------------------
         */
        } elseif (
            $centralAccountMode
            === 'developer'
        ) {

            $successDestination =
                route(
                    'marketplace.developer.library'
                );


            $showCentralUserActions =
                false;


        /*
         * --------------------------------------------------------
         * CENTRAL USER SUCCESS
         * --------------------------------------------------------
         *
         * No automatic Marketplace redirect.
         *
         * The popup itself presents:
         *
         * My Websites
         * Dashboard
         */
        } else {

            $successDestination =
                $centralDashboardUrl;


            $showCentralUserActions =
                true;
        }


        /*
         * ========================================================
         * UNIFIED PAYMENT POPUP VIEW
         * ========================================================
         *
         * Paid / failed / pending all use the same Blade popup.
         *
         * The Blade derives the visible payment state from the
         * canonical Marketplace payment_status.
         */
        if ($paymentIsPaid) {

            return view(
                'marketplace.payment-success',
                [
                    'order' =>
                        $record,

                    'paid' =>
                        true,

                    'entitlementActivated' =>
                        $record->status
                            === 'completed'
                        || $record->status
                            === 'fulfilled',

                    'deploymentType' =>
                        $deploymentType,

                    'successDestination' =>
                        $successDestination,

                    'showCentralUserActions' =>
                        $showCentralUserActions,

                    'centralMyWebsitesUrl' =>
                        $centralMyWebsitesUrl,

                    'centralDashboardUrl' =>
                        $centralDashboardUrl,
                ]
            );
        }


        return view(
            'marketplace.payment-success',
            [
                'order' =>
                    $record,

                'paid' =>
                    false,

                'entitlementActivated' =>
                    false,

                'deploymentType' =>
                    $deploymentType,

                /*
                 * SaaS/off-server:
                 * exact originating page.
                 *
                 * Central User/Developer:
                 * Pending Checkout.
                 */
                'successDestination' =>
                    $successDestination,

                'showCentralUserActions' =>
                    false,

                'centralMyWebsitesUrl' =>
                    $centralMyWebsitesUrl,

                'centralDashboardUrl' =>
                    $centralDashboardUrl,
            ]
        );
    }


    public function userOrders()
    {
        $orders = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_listings as listings',
                'listings.id',
                '=',
                'orders.marketplace_listing_id'
            )
            ->join(
                'marketplace_checkout_sessions as checkout_sessions',
                'checkout_sessions.marketplace_order_id',
                '=',
                'orders.id'
            )
            ->leftJoin(
                'websites',
                'websites.id',
                '=',
                'checkout_sessions.website_id'
            )
            ->where('orders.buyer_id', auth()->id())
            ->where('checkout_sessions.deployment_type', 'saas')
            ->select(
                'orders.*',
                'listings.title as product_title',
                'listings.product_type',
                'checkout_sessions.website_id',
                'websites.name as website_name',
                'websites.domain as website_domain',
                'websites.subdomain as website_subdomain'
            )
            ->latest('orders.created_at')
            ->get();

        return view('marketplace.user-orders', [
            'orders' => $orders,
        ]);
    }


    public function pendingCheckouts()
    {
        $orders = DB::table('marketplace_orders')
            ->join('marketplace_listings', 'marketplace_listings.id', '=', 'marketplace_orders.marketplace_listing_id')
            ->where('marketplace_orders.buyer_id', auth()->id())
            ->where('marketplace_orders.payment_status', 'pending')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('marketplace_checkout_sessions as checkout_sessions')
                    ->whereColumn(
                        'checkout_sessions.marketplace_order_id',
                        'marketplace_orders.id'
                    )
                    ->where(
                        'checkout_sessions.user_id',
                        auth()->id()
                    )
                    ->where(
                        'checkout_sessions.deployment_type',
                        'saas'
                    );
            })
            ->select(
                'marketplace_orders.*',
                'marketplace_listings.product_type',
                'marketplace_listings.title as listing_title',
                'marketplace_listings.slug as listing_slug'
            )
            ->latest('marketplace_orders.created_at')
            ->get();

        return view('marketplace.pending-checkouts', [
            'orders' => $orders,
        ]);
    }

    public function checkoutPage(int $order)
    {
        // ESUBIZ_DEVELOPER_BUILD_CHECKOUT_PAGE_V1
        //
        // Use a left join because Developer Builder purchases are
        // platform-owned orders and intentionally have no Marketplace
        // listing. Normal Marketplace products continue resolving through
        // their existing listing and MarketplaceProductResolver path.
        $order = DB::table('marketplace_orders')
            ->leftJoin(
                'marketplace_listings',
                'marketplace_listings.id',
                '=',
                'marketplace_orders.marketplace_listing_id'
            )
            ->where('marketplace_orders.id', $order)
            ->where('marketplace_orders.buyer_id', auth()->id())
            ->select(
                'marketplace_orders.*',
                'marketplace_listings.product_type',
                'marketplace_listings.product_id',
                'marketplace_listings.title as listing_title',
                'marketplace_listings.slug as listing_slug'
            )
            ->first();

        abort_unless($order, 404);

        $developerBuild = null;
        $resolver = null;
        $product = null;

        if ($order->developer_build_id) {
            $developerBuild = \App\Models\DeveloperBuild::query()
                ->where('id', $order->developer_build_id)
                ->where('developer_id', auth()->id())
                ->first();

            abort_unless($developerBuild, 404);

            $order->product_type = 'developer_build';
            $order->product_id = $developerBuild->id;
            $order->listing_title = $developerBuild->project_name;
            $order->listing_slug = $developerBuild->build_id;

            /*
             * Give the existing generic checkout view a product-shaped
             * object without creating a fake Marketplace product/listing.
             */
            $product = (object) [
                'id' => $developerBuild->id,
                'name' => $developerBuild->project_name,
                'title' => $developerBuild->project_name,
                'description' => 'Compiled off-server website',
            ];
        } else {
            $resolver = app(
                \App\Services\Marketplace\MarketplaceProductResolver::class
            );

            $product = $resolver->resolve(
                $order->product_type,
                (int) $order->product_id
            );

            abort_unless($product, 404);
        }

        /*
         * Unified checkout route:
         * resolve the deployment context from the originating checkout
         * session so the same /marketplace/checkout/{order} page serves
         * both SaaS and off-server purchases.
         */
        $checkoutSession = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $order->id)
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        $deploymentType = $checkoutSession->deployment_type
            ?? 'saas';

        abort_unless(
            in_array($deploymentType, ['saas', 'off_server'], true),
            422,
            'Invalid marketplace deployment type.'
        );

        if ($developerBuild) {
            abort_unless(
                $deploymentType === 'off_server',
                422,
                'Developer Build checkout requires off-server deployment.'
            );

            $price = (float) $order->amount;
            $currency = strtoupper($order->currency ?: 'NGN');
        } else {
            abort_unless(
                $resolver->available($product, $deploymentType),
                422,
                'This product is not available for the selected deployment type.'
            );

            $price = $resolver->price($product, $deploymentType);
            $currency = $resolver->currency($product, $deploymentType);

            abort_unless($price !== null, 422);
        }

        $onlineGateways = DB::table('payment_providers as providers')
            ->join(
                'payment_methods as methods',
                'methods.payment_provider_id',
                '=',
                'providers.id'
            )
            ->where('providers.is_active', true)
            ->whereNull('providers.deleted_at')
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->select(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->groupBy(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->orderBy('providers.name')
            ->get();

        $offlineMethods = DB::table('offline_payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        $wallet = DB::table('wallets')
            ->where('user_id', auth()->id())
            ->whereIn('type', ['customer', 'developer'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN type = 'customer' THEN 0 ELSE 1 END")
            ->first();

        if (!$wallet) {
            $walletId = DB::table('wallets')->insertGetId([
                'user_id' => auth()->id(),
                'workspace_id' => $order->workspace_id ?? null,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Esubiz Wallet',
                'type' => 'customer',
                'currency' => $currency ?: 'NGN',
                'available_balance' => 0,
                'pending_balance' => 0,
                'reserved_balance' => 0,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = DB::table('wallets')
                ->where('id', $walletId)
                ->first();
        }

        /*
         * Generic included marketplace product information.
         *
         * Bundles expose their included add-ons through the bundle-items
         * table. Standalone add-ons expose their configured capability
         * allocations through core_addon_capability_allocations.
         *
         * This is display-only and does not alter SaaS pricing.
         */
        $includedItems = collect();

        if (in_array($order->product_type, ['bundle', 'core_bundle', 'core-bundle'], true)) {
            $includedItems = DB::table('core_addon_bundle_items as items')
                ->join(
                    'core_addons as addons',
                    'addons.id',
                    '=',
                    'items.addon_id'
                )
                ->join(
                    'core_addon_bundles as bundles',
                    'bundles.id',
                    '=',
                    'items.bundle_id'
                )
                ->where('items.bundle_id', $order->product_id)
                ->where('addons.is_active', true)
                ->whereNull('addons.deleted_at')
                ->select(
                    'addons.id',
                    'addons.name',
                    'addons.description',
                    'addons.default_allocation',
                    'addons.allocation_unit',
                    'addons.is_unlimited',
                    'items.allocation',
                    'items.is_unlimited as bundle_is_unlimited',
                    'bundles.unit_name as bundle_unit_name'
                )
                ->orderBy('addons.name')
                ->get();

        } elseif (in_array($order->product_type, ['addon', 'core_addon', 'core-addon'], true)) {
            $includedItems = collect([
                (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'default_allocation' => $product->default_allocation,
                    'allocation_unit' => $product->allocation_unit,
                    'is_unlimited' => $product->is_unlimited,
                    'allocation' => null,
                    'bundle_is_unlimited' => false,
                    'capability_allocations' => DB::table(
                        'core_addon_capability_allocations'
                    )
                        ->where('addon_id', $product->id)
                        ->orderBy('id')
                        ->get(),
                ],
            ]);
        }

        return view('marketplace.checkout', [
            'order' => $order,
            'product' => $product,
            'productType' => $order->product_type,
            'onlineGateways' => $onlineGateways,
            'offlineMethods' => $offlineMethods,
            'saasCheckout' => $deploymentType === 'saas',
            'deploymentType' => $deploymentType,
            'checkoutContext' => $deploymentType,
            'price' => (float) $price,
            'currency' => $currency ?: 'NGN',
            'quantity' => 1,
            'includedItems' => $includedItems,
            'walletEnabled' => (bool) ($wallet->is_active ?? false),
            'walletBalance' => (float) ($wallet->available_balance ?? 0),
            'walletCurrency' => $wallet->currency ?? ($currency ?: 'NGN'),
        ]);
    }

    public function developer()
    {
        return view('marketplace.developer', [
            'catalogProducts' => $this->catalogProductsFor('off_server'),
            'addons' => DB::table('core_addons')
                ->where('is_active', true)
                ->where('off_server_available', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundles' => DB::table('core_addon_bundles')
                ->where('is_active', true)
                ->where('off_server_available', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
        ]);
    }


    /**
     * Consume a temporary signed checkout URL generated for a
     * registered off-server website.
     *
     * This is an ENTRY POINT only.
     *
     * Payment, order handling and fulfilment remain inside the
     * same Central Esubiz Marketplace checkout architecture.
     */
    public function externalCheckout(
        \Illuminate\Http\Request $request
    ) {
        /*
         * Route uses Laravel's signed middleware as the first
         * protection, but retain an explicit check as well.
         */
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This marketplace checkout link is invalid or has expired.'
        );


        $data =
            $request->validate([
                'website' => [
                    'required',
                    'uuid',
                ],

                'application' => [
                    'required',
                    'uuid',
                ],

                'product_type' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'product_id' => [
                    'required',
                    'integer',
                ],

                'return_area' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'return_url' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'token_id' => [
                    'required',
                    'uuid',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | WEBSITE + APPLICATION IDENTITY
        |--------------------------------------------------------------------------
        */

        $website =
            \App\Models\Website::query()
                ->where(
                    'uuid',
                    $data['website']
                )
                ->firstOrFail();


        $application =
            \App\Models\ApiApplication::query()
                ->where(
                    'uuid',
                    $data['application']
                )
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'is_verified',
                    true
                )
                ->firstOrFail();


        /*
         * External checkout entry is ONLY for developer/off-server
         * websites.
         */
        abort_unless(
            !empty(
                $website->developer_id
            ),
            403,
            'This checkout link is not valid for a SaaS website.'
        );


        /*
        |--------------------------------------------------------------------------
        | PRODUCT
        |--------------------------------------------------------------------------
        |
        | Never trust price or currency from the external website.
        | Resolve the product centrally and use OFF-SERVER pricing.
        |
        */

        $productResolver =
            app(
                \App\Services\Marketplace\MarketplaceProductResolver::class
            );


        $product =
            $productResolver->resolve(
                $data['product_type'],
                (int) $data['product_id']
            );


        abort_unless(
            $product,
            404
        );


        abort_unless(
            $productResolver->available(
                $product,
                'off_server'
            ),
            422,
            'This product is not available for off-server websites.'
        );


        $price =
            $productResolver->price(
                $product,
                'off_server'
            );


        $currency =
            $productResolver->currency(
                $product,
                'off_server'
            );


        abort_unless(
            $price !== null
            && (float) $price >= 0,
            422,
            'This product has no valid off-server price.'
        );


        /*
        |--------------------------------------------------------------------------
        | MARKETPLACE LISTING
        |--------------------------------------------------------------------------
        */

        $listing =
            $this->resolveMarketplaceListing(
                $data['product_type'],
                (int) $data['product_id']
            );


        abort_unless(
            $listing,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | RETURN DESTINATION
        |--------------------------------------------------------------------------
        |
        | The URL already passed validation when the signed checkout
        | link was generated from the application's registered origins.
        | The signature prevents alteration after generation.
        |
        */

        $returnUrl =
            trim(
                (string) (
                    $data['return_url']
                    ?? ''
                )
            );


        $returnUrl =
            $returnUrl !== ''
                ? $returnUrl
                : null;


        $returnArea =
            trim(
                (string) (
                    $data['return_area']
                    ?? 'marketplace'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | IDEMPOTENT LINK ENTRY
        |--------------------------------------------------------------------------
        |
        | Refreshing the same signed URL must not create multiple
        | pending marketplace orders.
        |
        */

        $existingSession =
            \Illuminate\Support\Facades\DB::table(
                'marketplace_checkout_sessions'
            )
                ->where(
                    'origin_token_id',
                    $data['token_id']
                )
                ->where(
                    'website_id',
                    $website->id
                )
                ->whereNull(
                    'deleted_at'
                )
                ->first();


        if (
            $existingSession
            && $existingSession->marketplace_order_id
        ) {
            return redirect()->route(
                'marketplace.checkout',
                [
                    'order' =>
                        $existingSession
                            ->marketplace_order_id,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE NORMAL CENTRAL MARKETPLACE ORDER
        |--------------------------------------------------------------------------
        */

        $orderReference =
            'MKT-'
            . strtoupper(
                bin2hex(
                    random_bytes(6)
                )
            );


        $amount =
            (float) $price;


        $orderId =
            \Illuminate\Support\Facades\DB::table(
                'marketplace_orders'
            )->insertGetId([
                'marketplace_listing_id' =>
                    $listing->id,

                'vendor_id' =>
                    $listing->vendor_id,

                'workspace_id' =>
                    $website->workspace_id,

                /*
                 * The purchase belongs to the registered developer
                 * account in Central Esubiz.
                 */
                'buyer_id' =>
                    $website->developer_id,

                'uuid' =>
                    (string)
                    \Illuminate\Support\Str::uuid(),

                'reference' =>
                    $orderReference,

                'amount' =>
                    $amount,

                'commission_amount' =>
                    0,

                'vendor_amount' =>
                    $amount,

                'currency' =>
                    strtoupper(
                        $currency
                        ?: 'NGN'
                    ),

                'status' =>
                    'pending',

                'payment_status' =>
                    'pending',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


        \Illuminate\Support\Facades\DB::table(
            'marketplace_checkout_sessions'
        )->insert([
            'user_id' =>
                $website->developer_id,

            'account_mode' =>
                'developer',

            'checkout_origin' =>
                'off_server_website',

            'return_url' =>
                $returnUrl,

            'return_area' =>
                $returnArea,

            /*
             * External website checkout NEVER exposes
             * Central Wallet.
             */
            'wallet_allowed' =>
                false,

            'origin_token_id' =>
                $data['token_id'],

            'deployment_type' =>
                'off_server',

            'product_type' =>
                $data['product_type'],

            'product_id' =>
                (int) $data['product_id'],

            'website_id' =>
                $website->id,

            'workspace_id' =>
                $website->workspace_id,

            'quantity' =>
                1,

            'unit_price' =>
                $amount,

            'total_amount' =>
                $amount,

            'currency' =>
                strtoupper(
                    $currency
                    ?: 'NGN'
                ),

            'is_commissionable' =>
                true,

            'marketplace_order_id' =>
                $orderId,

            'status' =>
                'pending_payment',

            'expires_at' =>
                now()->addHours(24),

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);


        return redirect()->route(
            'marketplace.checkout',
            [
                'order' =>
                    $orderId,
            ]
        );
    }


    /*
     * ESUBIZ_CENTRAL_THEME_MARKETPLACE_RESOLVER_V1
     *
     * Central Theme Marketplace must use the same eligibility source
     * as SaaS Core, off-server Core and the website wizards.
     */
    protected function marketplaceThemes(
        ThemeMarketplaceResolver $resolver,
        string $deployment = ThemeMarketplaceResolver::DEPLOYMENT_SAAS
    ) {
        return $resolver->forDeployment(
            $deployment
        );
    }


    /*
     * ESUBIZ_THEME_MARKETPLACE_CATALOG_V1
     *
     * Universal public Theme Marketplace catalog.
     *
     * Central Esubiz is the commercial source of truth.
     * SaaS and off-server Core installations must consume the same
     * eligibility contract rather than maintaining local commerce rules.
     */
    public function themeCatalog(
        \Illuminate\Http\Request $request,
        ThemeMarketplaceResolver $resolver
    ) {
        $deployment = strtolower(
            trim(
                (string) $request->query(
                    'deployment',
                    ThemeMarketplaceResolver::DEPLOYMENT_SAAS
                )
            )
        );

        if (
            !in_array(
                $deployment,
                [
                    ThemeMarketplaceResolver::DEPLOYMENT_SAAS,
                    ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER,
                ],
                true
            )
        ) {
            return response()->json(
                [
                    'ok' => false,
                    'message' => 'Invalid theme deployment context.',
                ],
                422
            );
        }

        $themes = $this->marketplaceThemes(
            $resolver,
            $deployment
        );

        $items = $themes
            ->map(
                function ($theme) use ($deployment) {
                    $isSaas =
                        $deployment
                        === ThemeMarketplaceResolver::DEPLOYMENT_SAAS;

                    return [
                        'id' => (int) $theme->id,
                        'uuid' => $theme->uuid,
                        'name' => $theme->name,
                        'slug' => $theme->slug,
                        'version' => $theme->version,

                        'publisher' => [
                            'name' => $theme->publisher_name,
                            'type' => $theme->publisher_type,
                        ],

                        'deployment' => $deployment,

                        'price' => $isSaas
                            ? $theme->saas_price
                            : $theme->off_server_price,

                        'currency' => $isSaas
                            ? $theme->saas_currency
                            : $theme->off_server_currency,

                        'billing' => $isSaas
                            ? [
                                'period' => $theme->saas_billing_period,
                                'interval' => $theme->saas_billing_interval,
                            ]
                            : null,

                        'marketplace' => [
                            'featured' => (bool) $theme->marketplace_featured,
                            'category' => $theme->marketplace_category,
                        ],

                        'package' => [
                            'checksum_sha256' => $theme->checksum_sha256,
                            'bytes' => (int) $theme->package_bytes,
                        ],
                    ];
                }
            )
            ->values();

        return response()->json(
            [
                'ok' => true,
                'deployment' => $deployment,
                'count' => $items->count(),
                'themes' => $items,
            ]
        );
    }

    /*
     * ESUBIZ_THEME_MARKETPLACE_PREVIEW_V1
     *
     * Public Theme preview asset delivery.
     *
     * Theme packages remain protected in storage.
     * Only the manifest-declared preview file may be exposed.
     */
    public function themePreview(
        int $themePackageId
    ) {
        $theme =
            \Illuminate\Support\Facades\DB::table(
                'theme_packages'
            )
                ->whereNull('deleted_at')
                ->where('id', $themePackageId)
                ->where('is_active', true)
                ->where('marketplace_ready', true)
                ->first();

        abort_unless(
            $theme,
            404,
            'Theme not found.'
        );

        $packagePath =
            app(
                \App\Services\Marketplace\ThemePackageService::class
            )->protectedRoot()
            . DIRECTORY_SEPARATOR
            . ltrim(
                (string) $theme->package_path,
                '/\\'
            );

        abort_unless(
            is_file($packagePath),
            404,
            'Theme package not found.'
        );

        $previewPath =
            ltrim(
                (string) $theme->preview_path,
                '/\\'
            );

        abort_unless(
            $previewPath !== '',
            404,
            'Theme preview is not configured.'
        );

        if (
            str_contains($previewPath, '..')
            || str_starts_with($previewPath, '/')
            || str_starts_with($previewPath, '\\')
        ) {
            abort(
                404,
                'Invalid Theme preview path.'
            );
        }

        $zip =
            new \ZipArchive();

        abort_unless(
            $zip->open($packagePath) === true,
            404,
            'Unable to open Theme package.'
        );

        $index =
            $zip->locateName(
                $previewPath,
                \ZipArchive::FL_NOCASE
            );

        if ($index === false) {
            $zip->close();

            abort(
                404,
                'Theme preview not found.'
            );
        }

        $contents =
            $zip->getFromIndex(
                $index
            );

        $entryName =
            $zip->getNameIndex(
                $index
            );

        $zip->close();

        abort_unless(
            is_string($contents),
            404,
            'Theme preview could not be read.'
        );

        $extension =
            strtolower(
                pathinfo(
                    (string) $entryName,
                    PATHINFO_EXTENSION
                )
            );

        $mime =
            match ($extension) {
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg',
                'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => null,
            };

        abort_unless(
            $mime !== null,
            404,
            'Unsupported Theme preview format.'
        );

        return response(
            $contents,
            200,
            [
                'Content-Type' =>
                    $mime,

                'Cache-Control' =>
                    'public, max-age=3600',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }


}
