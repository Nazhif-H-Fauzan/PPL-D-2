import apiFetch from './api-client';

const invoiceForm = document.getElementById('invoice-form');

if (invoiceForm) {
    const select = document.getElementById('terminId');
    const dpp = document.getElementById('dppAmount');
    const date = document.getElementById('invoiceDate');
    const alert = document.getElementById('invoice-alert');
    const button = document.getElementById('submit-invoice');
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
    let termins = [];
    let tax = { ppnRate: 11, pphRate: 2 };

    const show = (message, success = false) => {
        alert.textContent = message;
        alert.className = `mb-6 rounded-xl border px-4 py-3 text-sm ${success ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'}`;
    };
    const updatePreview = () => {
        const value = Number(dpp.value || 0);
        const ppn = value * Number(tax.ppnRate) / 100;
        const pph = value * Number(tax.pphRate) / 100;
        document.getElementById('tax-preview').classList.toggle('hidden', !value);
        document.getElementById('ppn-preview').textContent = money(ppn);
        document.getElementById('pph-preview').textContent = money(pph);
        document.getElementById('total-preview').textContent = money(value + ppn - pph);
    };
    const loadTax = async () => {
        const selected = termins.find(item => item.terminId === select.value);
        if (!selected) return;
        const response = await apiFetch(`/api/v1/tax-parameters/applicable?contractId=${encodeURIComponent(selected.contractId)}&date=${encodeURIComponent(date.value)}`);
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || 'Parameter pajak gagal dimuat.');
        tax = payload.data;
        updatePreview();
    };
    const load = async () => {
        try {
            const response = await apiFetch('/api/v1/termins?status=SIAP_DITAGIH&pageSize=100');
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message);
            termins = (payload.data.items || []).filter(item => item.poStatus === 'PO_ADA');
            select.innerHTML = '<option value="">Pilih termin</option>' + termins.map(item => `<option value="${item.terminId}" data-value="${item.terminValue}">${item.contractNumber} - Termin ${item.terminNumber} (${money(item.terminValue)})</option>`).join('');
            const requested = new URLSearchParams(location.search).get('terminId');
            if (requested) select.value = requested;
            if (select.value) dpp.value = select.options[select.selectedIndex].dataset.value;
            await loadTax();
        } catch (exception) {
            show(exception.message || 'Termin gagal dimuat.');
        }
    };
    select.addEventListener('change', async () => {
        dpp.value = select.options[select.selectedIndex]?.dataset.value || '';
        try { await loadTax(); } catch (exception) { show(exception.message); }
        updatePreview();
    });
    date.addEventListener('change', async () => {
        try { await loadTax(); } catch (exception) { show(exception.message); }
    });
    dpp.addEventListener('input', updatePreview);
    invoiceForm.addEventListener('submit', async event => {
        event.preventDefault();
        button.disabled = true;
        button.textContent = 'Menyimpan...';
        try {
            const response = await apiFetch('/api/v1/invoices', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ terminId: select.value, invoiceDate: date.value, dppAmount: dpp.value }) });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Invoice gagal dibuat.');
            location.href = `/invoices/${encodeURIComponent(payload.data.invoiceId)}`;
        } catch (exception) {
            show(exception.message);
            button.disabled = false;
            button.textContent = 'Simpan invoice';
        }
    });
    load();
}
