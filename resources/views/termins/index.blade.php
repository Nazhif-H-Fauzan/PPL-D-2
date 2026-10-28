@extends('layouts.app')

@section('content')
<div class="mb-7 flex flex-col justify-between gap-4 md:flex-row md:items-end">
    <div>
        <p class="mb-2 text-sm font-medium text-slate-500">Invoice per Termin</p>
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-950 md:text-3xl">Daftar Termin Siap Ditagih</h2>
        <p class="mt-2 text-sm text-slate-500">Pilih termin yang sudah memenuhi syarat untuk diterbitkan menjadi invoice.</p>
    </div>
    <button id="refresh-termins" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600">Muat ulang</button>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 md:flex-row md:items-center md:justify-between md:px-6">
        <div><h3 class="font-bold text-slate-950">Termin siap ditagih</h3><p id="termin-summary" class="mt-1 text-xs text-slate-500">Memuat data dari Modul D-2...</p></div>
        <label class="relative block"><span class="sr-only">Cari termin</span><input id="termin-search" type="search" placeholder="Cari kontrak atau proyek..." class="w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 md:w-72"></label>
    </div>
    <div id="termin-error" class="hidden m-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>
    <div class="overflow-x-auto"><table class="w-full min-w-[860px] text-left">
        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400"><tr><th class="px-6 py-3">Proyek</th><th class="px-6 py-3">Kontrak</th><th class="px-6 py-3">Termin</th><th class="px-6 py-3">Nilai termin</th><th class="px-6 py-3">Jatuh tempo</th><th class="px-6 py-3">Status PO</th><th class="px-6 py-3 text-right">Aksi</th></tr></thead>
        <tbody id="termin-rows" class="divide-y divide-slate-100 text-sm"><tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Memuat data...</td></tr></tbody>
    </table></div>
</section>
@endsection
