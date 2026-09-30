<style>
[data-ai-table-summary]::-webkit-details-marker { display: none; }
[data-ai-table-summary]:hover { background: #dbeafe; }
[data-ai-table-summary]:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
[data-ai-usage-table] [data-ai-table-chevron] { transition: transform .2s ease; }
[data-ai-usage-table][open] [data-ai-table-chevron] { transform: rotate(180deg); }
[data-ai-usage-table] [data-ai-table-close-hint] { display: none; }
[data-ai-usage-table][open] [data-ai-table-open-hint] { display: none; }
[data-ai-usage-table][open] [data-ai-table-close-hint] { display: inline; }
</style>
<div class="mt-6 grid gap-4 lg:grid-cols-2" data-ai-usage-tables
     data-url="{{ route('tenant.cms.ai.usage-data', ['subdomain' => request()->route('subdomain')]) }}">
    @foreach([
        'pricing' => ['title' => 'AI task price guide', 'description' => 'Estimated AI Credits for common tasks.', 'headings' => ['Task', 'Description', 'Estimated credits']],
        'usage' => ['title' => 'AI usage', 'description' => 'Credits used by this website’s AI tasks.', 'headings' => ['Task', 'Credits used', 'Balance after', 'Status', 'Date']],
    ] as $table => $config)
        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                 data-ai-usage-table="{{ $table }}">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-xl bg-blue-50 px-4 py-3 text-blue-700"
                     data-ai-table-summary>
                <span>
                    <span class="block text-base font-bold">{{ $config['title'] }}</span>
                    <span class="mt-1 block text-xs font-medium">
                        <span data-ai-table-open-hint>Open table</span>
                        <span data-ai-table-close-hint>Close table</span>
                    </span>
                </span>
                <svg data-ai-table-chevron class="h-5 w-5 shrink-0"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </summary>
            <p class="mt-2 text-sm text-slate-600">{{ $config['description'] }}</p>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[540px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            @foreach($config['headings'] as $heading)
                                <th class="px-3 py-3">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody data-ai-usage-rows>
                        <tr><td colspan="{{ count($config['headings']) }}" class="px-3 py-4 text-slate-500">Open to load records.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex items-center justify-between gap-2">
                <button type="button" data-ai-usage-prev disabled
                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold disabled:opacity-40">
                    Previous
                </button>
                <span data-ai-usage-page class="text-xs text-slate-600">Page 1</span>
                <button type="button" data-ai-usage-next disabled
                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold disabled:opacity-40">
                    Next
                </button>
            </div>
        </details>
    @endforeach
</div>
<script>
(() => {
    const root = document.currentScript.previousElementSibling;
    if (!root || !root.matches('[data-ai-usage-tables]')) return;
    const integer = value => new Intl.NumberFormat().format(Number(value));

    root.querySelectorAll('[data-ai-usage-table]').forEach(table => {
        const kind = table.dataset.aiUsageTable;
        const body = table.querySelector('[data-ai-usage-rows]');
        const previous = table.querySelector('[data-ai-usage-prev]');
        const next = table.querySelector('[data-ai-usage-next]');
        const label = table.querySelector('[data-ai-usage-page]');
        const columns = kind === 'pricing' ? 3 : 5;
        let page = 1;
        let loaded = false;
        let busy = false;

        function message(value) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = columns;
            td.className = 'px-3 py-4 text-slate-500';
            td.textContent = value;
            tr.append(td);
            body.replaceChildren(tr);
        }

        async function load(requested) {
            if (busy || requested < 1) return;
            busy = true;
            previous.disabled = next.disabled = true;
            message('Loading…');
            try {
                const url = new URL(root.dataset.url);
                url.searchParams.set('table', kind);
                url.searchParams.set(kind === 'pricing' ? 'pricing_page' : 'history_page', String(requested));
                const response = await fetch(url, {
                    headers: {'Accept': 'application/json'},
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Unable to load records.');
                const result = await response.json();
                if (!Array.isArray(result.rows)) throw new Error('Unable to load records.');
                page = Number(result.page) || 1;
                label.textContent = 'Page ' + page;
                if (!result.rows.length) {
                    message(kind === 'usage' ? 'No AI usage recorded yet.' : 'No task prices available.');
                } else {
                    const rows = result.rows.map(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-slate-100';
                        const values = kind === 'pricing'
                            ? [item.task, item.description, integer(item.minimum_credits) + '–' + integer(item.maximum_credits)]
                            : [item.task, integer(item.credits), item.balance_after === null ? '—' : integer(item.balance_after), item.status, item.date];
                        values.forEach(value => {
                            const td = document.createElement('td');
                            td.className = 'px-3 py-3 text-slate-700';
                            td.textContent = value ?? '—';
                            tr.append(td);
                        });
                        return tr;
                    });
                    body.replaceChildren(...rows);
                }
                previous.disabled = !result.has_previous;
                next.disabled = !result.has_next;
                loaded = true;
            } catch (error) {
                message('Could not load records. Close and open this table to retry.');
                loaded = false;
            } finally {
                busy = false;
            }
        }

        table.addEventListener('toggle', () => {
            if (table.open && !loaded) load(1);
        });
        previous.addEventListener('click', () => load(page - 1));
        next.addEventListener('click', () => load(page + 1));
    });
})();
</script>
