import apiFetch from './api-client';

const rows = document.getElementById('termin-rows');

if (rows) {
    const search = document.getElementById('termin-search');
    const summary = document.getElementById('termin-summary');
    const error = document.getElementById('termin-error');
    let items = [];
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
    const date = value => value ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value + 'T00:00:00')) : '-';
    const render = () => {
        const query = search.value.toLowerCase();
        const visible = items.filter(item => [item.projectId, item.contractNumber, item.terminId].join(' ').toLowerCase().includes(query));
        summary.textContent = `${visible.length} dari ${items.length} termin siap ditagih`;
        rows.innerHTML = visible.length ? visible.map(item => {
            const canInvoice = item.poStatus === 'PO_ADA';
            return `<tr class="transition hover:bg-slate-50">
                <td class="px-6 py-4 font-semibold text-slate-800">${item.projectId || '-'}</td>
                <td class="px-6 py-4 text-slate-500">${item.contractNumber}</td>
                <td class="px-6 py-4 text-slate-500">Termin ${item.terminNumber}</td>
                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-800">${money(item.terminValue)}</td>
                <td class="whitespace-nowrap px-6 py-4 text-slate-500">${date(item.dueDate)}</td>
                <td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold ${canInvoice ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600'}">${item.poStatus || 'Tidak tersedia'}</span></td>
                <td class="px-6 py-4 text-right">${canInvoice ? `<a href="/invoices?terminId=${encodeURIComponent(item.terminId)}" class="inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-indigo-700">Buat invoice</a>` : '<span class="text-xs font-medium text-slate-400">PO belum ada</span>'}</td>
            </tr>`;
        }).join('') : '<tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Tidak ada termin yang sesuai.</td></tr>';
    };
    const load = async () => {
        error.classList.add('hidden');
        rows.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Memuat data...</td></tr>';
        try {
            const response = await apiFetch('/api/v1/termins?status=SIAP_DITAGIH&pageSize=100');
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Daftar termin gagal dimuat.');
            items = payload.data.items || [];
            render();
        } catch (exception) {
            error.textContent = exception.message;
            error.classList.remove('hidden');
            rows.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Data belum dapat dimuat.</td></tr>';
        }
    };
    search.addEventListener('input', render);
    document.getElementById('refresh-termins').addEventListener('click', load);
    load();
}
