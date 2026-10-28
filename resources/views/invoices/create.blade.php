@extends('layouts.app')

@section('content')
<div class="mb-7"><p class="mb-2 text-sm font-medium text-slate-500">Invoice per Termin</p><h2 class="text-2xl font-extrabold tracking-tight text-slate-950 md:text-3xl">Buat Invoice dari Termin</h2><p class="mt-2 text-sm text-slate-500">Sistem akan menghitung PPN, PPh, dan total tagihan otomatis berdasarkan parameter pajak yang berlaku.</p></div>
<div id="invoice-alert" class="mb-6 hidden rounded-xl border px-4 py-3 text-sm" role="alert"></div>
<form id="invoice-form" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
    <div class="mb-7 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800"><p class="font-bold">Pilih termin yang akan ditagihkan</p><p class="mt-1 text-xs text-indigo-600">Termin dengan PO yang sudah tersedia dapat diterbitkan menjadi invoice.</p></div>
    <div class="grid gap-5 md:grid-cols-2">
        <label class="block md:col-span-2"><span class="mb-2 block text-sm font-bold text-slate-700">Termin <span class="text-rose-500">*</span></span><select id="terminId" name="terminId" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"><option value="">Memuat termin...</option></select></label>
        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Tanggal invoice <span class="text-rose-500">*</span></span><input id="invoiceDate" name="invoiceDate" type="date" required value="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></label>
        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">DPP (nilai sebelum pajak) <span class="text-rose-500">*</span></span><input id="dppAmount" name="dppAmount" type="number" min="1" step="0.01" required placeholder="0" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></label>
    </div>
    <div id="tax-preview" class="mt-6 hidden grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-3"><div><p class="text-xs text-slate-500">PPN</p><p id="ppn-preview" class="mt-1 font-bold text-slate-800">-</p></div><div><p class="text-xs text-slate-500">PPh</p><p id="pph-preview" class="mt-1 font-bold text-slate-800">-</p></div><div><p class="text-xs text-slate-500">Total tagihan</p><p id="total-preview" class="mt-1 font-bold text-indigo-700">-</p></div></div>
    <div class="mt-8 flex flex-col-reverse justify-end gap-3 sm:flex-row"><a href="{{ route('termins') }}" class="inline-flex justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</a><button id="submit-invoice" type="submit" class="inline-flex justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 disabled:opacity-50">Simpan invoice</button></div>
</form>
@endsection
