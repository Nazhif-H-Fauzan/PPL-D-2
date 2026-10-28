import apiFetch from './api-client';

const invoiceDetail = document.getElementById('invoice-detail');

if (invoiceDetail) {
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
    const date = value => value ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value)) : '-';
    const invoiceId = invoiceDetail.dataset.invoiceId;
    apiFetch(`/api/v1/invoices/${encodeURIComponent(invoiceId)}`).then(async response => {
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || 'Detail invoice gagal dimuat.');
        const item = payload.data;
        const relation = item.terkait || {};
        const history = item.riwayat || [];
        invoiceDetail.innerHTML = `<div class="flex flex-col justify-between gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-start"><div><p class="mb-2 text-sm font-medium text-slate-500">Invoice per Termin</p><h2 class="text-2xl font-extrabold text-slate-950">${item.invoiceNumber}</h2><p class="mt-2 text-sm text-slate-500">Diterbitkan ${date(item.invoiceDate)}</p></div><span class="w-fit rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">${item.status || 'TERTAGIH'}</span></div><div class="mt-6 grid gap-6 lg:grid-cols-2"><div><h3 class="font-bold text-slate-950">Ringkasan tagihan</h3><dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">${[['Nilai invoice', item.dppAmount], ['PPN', item.ppnAmount], ['PPh', item.pphAmount], ['Total tagihan', item.totalAmount]].map(([label, value], index) => `<div class="flex justify-between px-4 py-3 text-sm ${index === 3 ? 'font-bold text-indigo-700' : ''}"><dt class="text-slate-500">${label}</dt><dd>${money(value)}</dd></div>`).join('')}</dl></div><div><h3 class="font-bold text-slate-950">Keterkaitan</h3><dl class="mt-4 space-y-3 rounded-xl bg-slate-50 p-4 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-500">Termin</dt><dd class="font-semibold text-slate-800">${relation.terminId || '-'}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Kontrak</dt><dd class="font-semibold text-slate-800">${relation.contractNumber || relation.contractId || '-'}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Status PO</dt><dd class="font-semibold text-slate-800">${relation.poStatus || '-'}</dd></div></dl></div></div><div class="mt-8"><h3 class="font-bold text-slate-950">Riwayat status</h3><div class="mt-4 space-y-3">${history.length ? history.map(entry => `<div class="flex gap-3 rounded-xl border border-slate-100 p-4 text-sm"><div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500"></div><div><p class="font-bold text-slate-800">${entry.status}</p><p class="mt-1 text-xs text-slate-500">${entry.note || 'Status invoice diperbarui'} - ${date(entry.changedAt)}</p></div></div>`).join('') : '<p class="text-sm text-slate-500">Belum ada riwayat status.</p>'}</div></div><div class="mt-8"><a href="/termins" class="inline-flex rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">Kembali ke daftar termin</a></div>`;
    }).catch(exception => {
        invoiceDetail.innerHTML = `<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">${exception.message}</div>`;
    });
}
