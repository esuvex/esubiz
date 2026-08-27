<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $paymentPresentation['title'] ?? 'Payment Status' }}
        · Esubiz
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 24px;

            background:
                rgba(15, 23, 42, .48);

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .esubiz-payment-popup {
            width: min(430px, 100%);

            background: #ffffff;

            border-radius: 22px;

            box-shadow:
                0 30px 80px
                rgba(15, 23, 42, .28);

            overflow: hidden;
        }

        .esubiz-payment-popup-header {
            display: flex;
            justify-content: flex-end;

            padding: 14px 16px 0;
        }

        .esubiz-payment-close-x {
            width: 36px;
            height: 36px;

            border: 0;
            border-radius: 50%;

            background: #f1f5f9;
            color: #334155;

            cursor: pointer;

            font-size: 22px;
            line-height: 1;
        }

        .esubiz-payment-content {
            padding:
                4px 34px 34px;

            text-align: center;
        }

        .esubiz-payment-icon {
            width: 72px;
            height: 72px;

            margin:
                4px auto 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            font-size: 32px;
            font-weight: 900;
        }

        .esubiz-payment-icon-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .esubiz-payment-icon-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .esubiz-payment-icon-failed {
            background: #fee2e2;
            color: #dc2626;
        }

        .esubiz-payment-title {
            margin: 0 0 9px;

            color: #0f172a;

            font-size: 24px;
            font-weight: 850;
        }

        .esubiz-payment-description {
            margin: 0 auto 24px;

            max-width: 330px;

            color: #64748b;

            font-size: 14px;
            line-height: 1.65;
        }

        .esubiz-payment-summary {
            margin-bottom: 24px;

            padding: 15px 17px;

            border:
                1px solid #e2e8f0;

            border-radius: 14px;

            background: #f8fafc;

            text-align: left;
        }

        .esubiz-payment-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;

            padding: 8px 0;

            color: #64748b;

            font-size: 13px;
        }

        .esubiz-payment-row + .esubiz-payment-row {
            border-top:
                1px solid #e2e8f0;
        }

        .esubiz-payment-row strong {
            color: #0f172a;

            text-align: right;
        }

        .esubiz-payment-status-paid {
            color: #16a34a !important;
        }

        .esubiz-payment-status-pending {
            color: #d97706 !important;
        }

        .esubiz-payment-status-failed {
            color: #dc2626 !important;
        }

        .esubiz-payment-entitlement {
            margin-bottom: 22px;

            padding: 12px 15px;

            border-radius: 12px;

            background: #ecfdf5;
            color: #047857;

            font-size: 13px;
            font-weight: 700;

            line-height: 1.5;
        }

        .esubiz-payment-close-button {
            width: 100%;

            border: 0;
            border-radius: 12px;

            padding: 14px 18px;

            background: #2563eb;
            color: #ffffff;

            cursor: pointer;

            font: inherit;
            font-size: 14px;
            font-weight: 800;
        }

        .esubiz-payment-close-button:hover {
            background: #1d4ed8;
        }

        .esubiz-payment-note {
            margin-top: 13px;

            color: #94a3b8;

            font-size: 12px;
        }
    </style>
</head>

<body>

    @php
        /*
         * successDestination is resolved centrally from the
         * authoritative Marketplace checkout session.
         *
         * SaaS:
         * returns to exact originating SaaS page.
         *
         * Off-server:
         * returns to exact validated external page.
         *
         * Central Esubiz:
         * returns to its appropriate Central fallback.
         */
        $returnDestination =
            $successDestination
            ?? route('marketplace.index');


        /*
         * Carry payment outcome back to the originating page for UI
         * feedback only.
         *
         * This value must never be used as proof of payment or
         * fulfilment. Central Marketplace remains authoritative.
         */
        $returnPaymentState =
            $paid
                ? 'success'
                : (
                    strtolower(
                        (string) (
                            $order->payment_status
                            ?? 'pending'
                        )
                    ) === 'failed'
                        ? 'failed'
                        : 'pending'
                );


        if (
            is_string($returnDestination)
            && filter_var(
                $returnDestination,
                FILTER_VALIDATE_URL
            )
        ) {
            $separator =
                str_contains(
                    $returnDestination,
                    '?'
                )
                    ? '&'
                    : '?';

            $returnDestination .=
                $separator
                . 'marketplace_payment='
                . urlencode(
                    $returnPaymentState
                )
                . '&marketplace_order='
                . urlencode(
                    (string) (
                        $order->id
                        ?? ''
                    )
                );
        }
    

        /*
         * CHECKPOINT_6_PAYMENT_PRESENTATION
         *
         * UI state only.
         * Central order/payment status remains authoritative.
         */
        $rawPaymentStatus =
            strtolower(
                trim(
                    (string) (
                        $order->payment_status
                        ?? 'pending'
                    )
                )
            );

        $returnPaymentState =
            $paid
                ? 'success'
                : (
                    in_array(
                        $rawPaymentStatus,
                        [
                            'failed',
                            'declined',
                            'cancelled',
                            'canceled',
                            'abandoned',
                            'error',
                        ],
                        true
                    )
                        ? 'failed'
                        : 'pending'
                );

        $paymentPresentation =
            match ($returnPaymentState) {

                'success' => [
                    'title' =>
                        'Payment Successful',

                    'description' =>
                        'Your payment was received successfully and your purchase has been processed.',

                    'symbol' =>
                        '✓',

                    'icon_class' =>
                        'esubiz-payment-icon-success',

                    'status_class' =>
                        'esubiz-payment-status-paid',

                    'status_label' =>
                        'SUCCESS',
                ],

                'failed' => [
                    'title' =>
                        'Payment Failed',

                    'description' =>
                        'Your payment was not completed. No purchase will be fulfilled for this failed payment.',

                    'symbol' =>
                        '×',

                    'icon_class' =>
                        'esubiz-payment-icon-failed',

                    'status_class' =>
                        'esubiz-payment-status-failed',

                    'status_label' =>
                        'FAILED',
                ],

                default => [
                    'title' =>
                        'Payment Pending',

                    'description' =>
                        'Your payment has not been confirmed yet. No fulfilment will occur until Esubiz confirms payment.',

                    'symbol' =>
                        '!',

                    'icon_class' =>
                        'esubiz-payment-icon-pending',

                    'status_class' =>
                        'esubiz-payment-status-pending',

                    'status_label' =>
                        'PENDING',
                ],
            };

@endphp


    <div
        class="esubiz-payment-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="esubiz-payment-title"
    >

        <div class="esubiz-payment-popup-header">

            <button
                type="button"
                class="esubiz-payment-close-x"
                data-payment-close
                aria-label="Close"
            >
                ×
            </button>

        </div>


        <div class="esubiz-payment-content">

            <div
                class="
                    esubiz-payment-icon
                    {{ $paymentPresentation['icon_class'] }}
                "
            >
                {{ $paymentPresentation['symbol'] }}
            </div>

            <h1
                id="esubiz-payment-title"
                class="esubiz-payment-title"
            >
                {{ $paymentPresentation['title'] }}
            </h1>

            <p class="esubiz-payment-description">
                {{ $paymentPresentation['description'] }}
            </p>

<div class="esubiz-payment-summary">

                <div class="esubiz-payment-row">

                    <span>
                        Order Reference
                    </span>

                    <strong>
                        {{ $order->reference ?? '—' }}
                    </strong>

                </div>


                <div class="esubiz-payment-row">

                    <span>
                        Payment Status
                    </span>

                    <strong
                        class="{{
                            $paymentPresentation[
                                'status_class'
                            ]
                        }}"
                    >
                        {{
                            $paymentPresentation[
                                'status_label'
                            ]
                        }}
                    </strong>

                </div>

            </div>


            @if(
                $paid
                && $entitlementActivated
            )

                <div class="esubiz-payment-entitlement">
                    Your purchase has been fulfilled successfully.
                </div>

            @endif


            @if($showCentralUserActions ?? false)

                <div
                    style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:10px;
                    "
                >
                    <a
                        href="{{
                            $centralMyWebsitesUrl
                            ?? url('/websites')
                        }}"
                        class="esubiz-payment-close-button"
                        style="
                            text-decoration:none;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                        "
                    >
                        My Websites
                    </a>

                    <a
                        href="{{
                            $centralDashboardUrl
                            ?? url('/dashboard')
                        }}"
                        class="esubiz-payment-close-button"
                        style="
                            text-decoration:none;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                        "
                    >
                        Dashboard
                    </a>
                </div>

                <div class="esubiz-payment-note">
                    Continue to My Websites or your Dashboard.
                </div>

            @else

                <button
                    type="button"
                    class="esubiz-payment-close-button"
                    data-payment-close
                >
                    Close
                </button>

                <div class="esubiz-payment-note">
                    Close to return to where you started checkout.
                </div>

            @endif

        </div>

    </div>


    <script>
        (function () {

            const returnUrl =
                @json($returnDestination);


            function closePaymentPopup() {

                if (
                    typeof returnUrl !== 'string'
                    || returnUrl.trim() === ''
                ) {
                    window.location.href =
                        '/marketplace';

                    return;
                }


                /*
                 * replace() prevents the payment callback page
                 * from remaining in browser history.
                 *
                 * Pressing Back after returning therefore does
                 * not accidentally reopen/reprocess payment.
                 */
                window.location.replace(
                    returnUrl
                );
            }


            document
                .querySelectorAll(
                    '[data-payment-close]'
                )
                .forEach(
                    function (button) {

                        button.addEventListener(
                            'click',
                            closePaymentPopup
                        );
                    }
                );


            /*
             * Escape behaves like Close on desktop.
             */
            document.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key === 'Escape'
                    ) {
                        closePaymentPopup();
                    }
                }
            );

        })();
    </script>

</body>
</html>
