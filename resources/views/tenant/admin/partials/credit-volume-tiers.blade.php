<details
    class="rounded-xl border border-slate-200 bg-white p-3"
    data-credit-price-tiers
    data-credit-type="{{ $creditType }}"
    data-tiers-url="{{ route('tenant.cms.credits.tiers', ['subdomain' => request()->route('subdomain')]) }}"
>
    <summary class="cursor-pointer text-sm font-bold text-blue-700">
        Price guide — see costs by quantity
    </summary>
    <p class="mt-1 text-xs text-slate-600">
        The price per credit depends on how many you buy. Your total appears below.
    </p>

    <div class="mt-3 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-200 text-slate-600">
                <tr>
                    <th class="px-2 py-2">Credit min</th>
                    <th class="px-2 py-2">Credit max</th>
                    <th class="px-2 py-2">Price per credit</th>
                </tr>
            </thead>
            <tbody data-tier-rows>
                <tr><td colspan="3" class="px-2 py-3 text-slate-500">Loading prices…</td></tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex items-center justify-between gap-2">
        <button type="button" data-tier-prev disabled
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold disabled:opacity-40">
            Previous
        </button>
        <span data-tier-page class="text-xs text-slate-600">Page 1</span>
        <button type="button" data-tier-next disabled
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold disabled:opacity-40">
            Next
        </button>
    </div>
</details>

<script>
(() => {
    const root = document.currentScript.previousElementSibling;
    if (!root || !root.matches('[data-credit-price-tiers]')) return;

    const body = root.querySelector('[data-tier-rows]');
    const previous = root.querySelector('[data-tier-prev]');
    const next = root.querySelector('[data-tier-next]');
    const pageLabel = root.querySelector('[data-tier-page]');
    let page = 1;
    let loading = false;

    const number = value => new Intl.NumberFormat().format(Number(value));
    const message = value => {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 3;
        cell.className = 'px-2 py-3 text-slate-600';
        cell.textContent = value;
        row.appendChild(cell);
        body.replaceChildren(row);
    };

    async function loadPage(requested) {
        if (loading || requested < 1) return;
        loading = true;
        previous.disabled = true;
        next.disabled = true;
        message('Loading prices…');

        try {
            const url = new URL(root.dataset.tiersUrl);
            url.searchParams.set('credit_type', root.dataset.creditType);
            url.searchParams.set('page', String(requested));

            const response = await fetch(url, {
                headers: {'Accept': 'application/json'}
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Prices are unavailable.');

            page = Number(result.page) || 1;
            pageLabel.textContent = 'Page ' + page;

            if (!Array.isArray(result.tiers) || result.tiers.length === 0) {
                message('No prices have been set for this credit yet.');
            } else {
                const rows = result.tiers.map(tier => {
                    const row = document.createElement('tr');
                    row.className = 'border-b border-slate-100';
                    const values = [
                        number(tier.minimum),
                        tier.maximum === null ? 'No upper limit' : number(tier.maximum),
                        result.currency + ' ' +
                            new Intl.NumberFormat(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(Number(tier.unit_price)),
                    ];
                    for (const value of values) {
                        const cell = document.createElement('td');
                        cell.className = 'px-2 py-2 text-slate-700';
                        cell.textContent = value;
                        row.appendChild(cell);
                    }
                    return row;
                });
                body.replaceChildren(...rows);
            }

            previous.disabled = !result.has_previous;
            next.disabled = !result.has_next;
        } catch (error) {
            message('Prices could not be loaded. Please try again.');
        } finally {
            loading = false;
        }
    }

    previous.addEventListener('click', () => loadPage(page - 1));
    next.addEventListener('click', () => loadPage(page + 1));
    root.addEventListener('toggle', () => {
        if (root.open && !root.dataset.loaded) {
            root.dataset.loaded = '1';
            loadPage(1);
        }
    });
})();
</script>
