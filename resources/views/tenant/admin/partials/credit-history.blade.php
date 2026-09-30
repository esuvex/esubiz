<details
    class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-3"
    data-credit-history
    data-credit-type="{{ $creditType }}"
    data-history-url="{{ route('tenant.cms.credits.history', ['subdomain' => request()->route('subdomain')]) }}"
>
    <summary class="cursor-pointer text-sm font-bold text-blue-700">
        Purchase and usage — view credit activity
    </summary>

    <div class="mt-3 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="border-b border-blue-100 text-slate-600">
                <tr>
                    <th class="px-2 py-2">Date</th>
                    <th class="px-2 py-2">Activity</th>
                    <th class="px-2 py-2">Credits</th>
                    <th class="px-2 py-2">Balance</th>
                </tr>
            </thead>
            <tbody data-history-rows>
                <tr><td colspan="4" class="px-2 py-3 text-slate-500">Open to load activity.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex items-center justify-between gap-2">
        <button type="button" data-history-prev disabled
            class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold disabled:opacity-40">
            Previous
        </button>
        <span data-history-page class="text-xs text-slate-600">Page 1</span>
        <button type="button" data-history-next disabled
            class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold disabled:opacity-40">
            Next
        </button>
    </div>
</details>

<script>
(() => {
    const root = document.currentScript.previousElementSibling;
    if (!root || !root.matches('[data-credit-history]')) return;

    const body = root.querySelector('[data-history-rows]');
    const previous = root.querySelector('[data-history-prev]');
    const next = root.querySelector('[data-history-next]');
    const pageLabel = root.querySelector('[data-history-page]');
    let page = 1;
    let loading = false;

    const message = value => {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
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
        message('Loading activity…');

        try {
            const url = new URL(root.dataset.historyUrl);
            url.searchParams.set('credit_type', root.dataset.creditType);
            url.searchParams.set('page', String(requested));
            const response = await fetch(url, {
                headers: {'Accept': 'application/json'}
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Activity unavailable.');

            page = Number(result.page) || 1;
            pageLabel.textContent = 'Page ' + page;

            if (!Array.isArray(result.items) || result.items.length === 0) {
                message('No credit activity yet.');
            } else {
                body.replaceChildren(...result.items.map(item => {
                    const row = document.createElement('tr');
                    row.className = 'border-b border-blue-100';
                    const values = [
                        item.date,
                        item.type,
                        new Intl.NumberFormat().format(Number(item.amount)),
                        new Intl.NumberFormat().format(Number(item.balance_after))
                    ];
                    values.forEach(value => {
                        const cell = document.createElement('td');
                        cell.className = 'px-2 py-2 text-slate-700';
                        cell.textContent = value;
                        row.appendChild(cell);
                    });
                    return row;
                }));
            }

            previous.disabled = !result.has_previous;
            next.disabled = !result.has_next;
        } catch (error) {
            message('Activity could not be loaded. Please try again.');
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
