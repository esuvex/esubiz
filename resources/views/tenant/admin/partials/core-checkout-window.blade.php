{{-- ESUBIZ_CORE_CHECKOUT_WINDOW_V1 --}}
{{-- ESUBIZ_CHECKOUT_EARLY_READY_V1 --}}
<link rel="dns-prefetch" href="//esubiz.com">
<link rel="preconnect" href="https://esubiz.com">

<dialog id="esubiz-core-checkout-window"
    aria-labelledby="esubiz-core-checkout-title"
    style="width:min(1100px,94vw);height:88vh;padding:0;border:1px solid #dbeafe;border-radius:18px;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:#2563eb;color:white;">
        <h2 id="esubiz-core-checkout-title" style="font-weight:700;">Checkout</h2>
        <button type="button" data-core-checkout-close
            style="padding:6px 12px;" aria-label="Close checkout">Close</button>
    </div>
    <div data-core-checkout-loading role="status" aria-live="polite" hidden
        style="position:absolute;inset:62px 0 0;z-index:2;background:white;align-items:center;justify-content:center;text-align:center;padding:24px;">
        <div>
            <div style="font-size:28px;color:#2563eb;">⏳</div>
            <p data-core-checkout-loading-text style="margin-top:12px;font-weight:700;color:#0f172a;">Opening checkout…</p>
            <p style="margin-top:8px;font-size:14px;color:#64748b;">Please keep this window open.</p>
        </div>
    </div>
    <iframe name="esubiz-core-checkout-frame"
        title="Esubiz secure checkout" referrerpolicy="no-referrer"
        style="display:block;width:100%;height:calc(100% - 62px);border:0;background:white;"></iframe>
</dialog>
<style>
#esubiz-core-checkout-window::backdrop { background:rgba(15,23,42,.55); }
</style>
<script>
(() => {
    const dialog = document.getElementById('esubiz-core-checkout-window');
    const frame = dialog.querySelector('iframe');
    let previousFocus;
    const loading = dialog.querySelector('[data-core-checkout-loading]');
    const loadingText = dialog.querySelector('[data-core-checkout-loading-text]');
    let loadingTimer;

    function showLoading(message) {
        clearTimeout(loadingTimer);
        loadingText.textContent = message;
        loading.hidden = false;
        loading.style.display = 'flex';
        loadingTimer = setTimeout(() => {
            loadingText.textContent = 'This is taking a little longer. Please wait…';
        }, 15000);
    }

    frame.addEventListener('load', () => {
        clearTimeout(loadingTimer);
        loading.hidden = true;
        loading.style.display = 'none';
    });

    window.addEventListener('message', event => {
        if (event.source !== frame.contentWindow
            || event.origin !== 'https://esubiz.com') return;

        if (event.data?.type === 'esubiz-checkout-ready') {
            clearTimeout(loadingTimer);
            loading.hidden = true;
            loading.style.display = 'none';
            return;
        }

        if (event.data?.type === 'esubiz-checkout-payment-loading') {
            showLoading('Opening your payment method…');
        }
    });

    function show() {
        if (!dialog.open) {
            previousFocus = document.activeElement;
            dialog.showModal();
        }
    }

    window.EsubizCoreCheckout = {
        open(rawUrl) {
            const url = new URL(rawUrl, window.location.href);
            if (url.protocol !== 'https:' || url.hostname !== 'esubiz.com'
                || !url.pathname.startsWith('/marketplace/')) {
                throw new Error('This checkout link is unavailable.');
            }
            showLoading('Opening checkout…');
            show();
            frame.src = url.href;
        },
        prepareForm(form) {
            form.target = frame.name;
            showLoading('Opening checkout…');
            show();
        }
    };


    /*
     * Existing quantity validation runs before this document listener.
     * Submit valid credit purchases into the checkout window.
     */
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
        const action = new URL(form.action, window.location.href);
        if (action.origin !== window.location.origin
            || action.pathname !== '/admin/credits/checkout') return;
        window.EsubizCoreCheckout.prepareForm(form);
    });

    dialog.querySelector('[data-core-checkout-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        if (previousFocus && previousFocus.isConnected) previousFocus.focus();
    });
})();
</script>


<script>
// CORE_PAYMENT_PARENT_CLOSE_V1
window.addEventListener('message', function (event) {
    const checkoutDialog = document.getElementById('esubiz-core-checkout-window');
    const checkoutFrame = checkoutDialog && checkoutDialog.querySelector('iframe');

    if (!checkoutFrame
        || event.source !== checkoutFrame.contentWindow
        || event.origin !== 'https://esubiz.com'
        || !event.data
        || event.data.type !== 'esubiz-core-payment-close') {
        return;
    }

    if (checkoutDialog.open) checkoutDialog.close();
    window.location.reload();
});
</script>
