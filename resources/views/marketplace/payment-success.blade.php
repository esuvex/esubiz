<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $paid ? 'Payment Successful' : 'Payment Status' }}
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

            @if($paid)

                <div
                    class="
                        esubiz-payment-icon
                        esubiz-payment-icon-success
                    "
                >
                    ✓
                </div>

                <h1
                    id="esubiz-payment-title"
                    class="esubiz-payment-title"
                >
                    Payment Successful
                </h1>

                <p class="esubiz-payment-description">
                    Your payment was received successfully
                    and your Marketplace purchase has been
                    processed.
                </p>

            @else

                <div
                    class="
                        esubiz-payment-icon
                        esubiz-payment-icon-pending
                    "
                >
                    !
                </div>

                <h1
                    id="esubiz-payment-title"
                    class="esubiz-payment-title"
                >
                    Payment Pending
                </h1>

                <p class="esubiz-payment-description">
                    Your payment has not been confirmed yet.
                    You can return to your website and check
                    the purchase again shortly.
                </p>

            @endif


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
                            $paid
                                ? 'esubiz-payment-status-paid'
                                : 'esubiz-payment-status-pending'
                        }}"
                    >
                        {{
                            $paid
                                ? 'PAID'
                                : strtoupper(
                                    $order->payment_status
                                    ?? 'PENDING'
                                )
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


            <button
                type="button"
                class="esubiz-payment-close-button"
                data-payment-close
            >
                Close
            </button>


            <div class="esubiz-payment-note">
                You will return to where you started checkout.
            </div>

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
