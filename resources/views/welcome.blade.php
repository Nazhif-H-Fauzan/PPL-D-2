@extends('layouts.app', ['title' => 'Overview | InvoiceKu', 'pageHeading' => 'Overview'])

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="mb-2 text-sm font-medium text-slate-500">Rabu, 28 Oktober 2026</p>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-950 md:text-3xl">Ringkasan aktivitas</h2>
            <p class="mt-2 text-sm text-slate-500">Pantau proyek dan invoice per termin kamu di satu tempat.</p>
        </div>
        <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14m-7-7h14"/></svg>
            Buat invoice
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Total proyek', 'value' => '24', 'change' => '+8.2%', 'caption' => 'dari bulan lalu', 'tone' => 'bg-indigo-50 text-indigo-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5v11.25H3.75V7.5Zm3-3h4.5l1.5 3h7.5"/>'],
            ['label' => 'Kontrak aktif', 'value' => '18', 'change' => '+4.5%', 'caption' => 'dari bulan lalu', 'tone' => 'bg-emerald-50 text-emerald-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h7.879L19.5 7.871V20.25h-12V3.75Zm7.5.75v4.5h4.5M10.5 12h6"/>'],
            ['label' => 'Invoice tertunda', 'value' => '12', 'change' => '-2.4%', 'caption' => 'dari bulan lalu', 'tone' => 'bg-amber-50 text-amber-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A1.75 1.75 0 0 1 18.25 5.5v15l-3.25-1.75-3.25 1.75-3.25-1.75-3.25 1.75v-15A1.75 1.75 0 0 1 7.5 3.75ZM9 9h6"/>'],
            ['label' => 'Total pendapatan', 'value' => 'Rp 1,2 M', 'change' => '+12.8%', 'caption' => 'dari bulan lalu', 'tone' => 'bg-violet-50 text-violet-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m6-15.5H9.75a2.75 2.75 0 1 0 0 5.5h4.5a2.75 2.75 0 1 1 0 5.5H6"/>'],
        ] as $stat)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $stat['icon'] !!}</svg>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-600">{{ $stat['change'] }}</span>
                </div>
                <p class="mt-5 text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $stat['caption'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 md:px-6">
                <div>
                    <h3 class="font-bold text-slate-950">Invoice terbaru</h3>
                    <p class="mt-1 text-xs text-slate-500">Daftar invoice yang terakhir diperbarui</p>
                </div>
                <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <tr><th class="px-6 py-3">Nomor invoice</th><th class="px-6 py-3">Proyek</th><th class="px-6 py-3">Tanggal</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Nilai</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach ([['id' => 'INV-2026-0012', 'project' => 'Renovasi Gedung A', 'date' => '25 Okt 2026', 'status' => 'Menunggu', 'amount' => 'Rp 86.500.000'], ['id' => 'INV-2026-0011', 'project' => 'Pembangunan Jalan Tol', 'date' => '22 Okt 2026', 'status' => 'Dibayar', 'amount' => 'Rp 120.000.000'], ['id' => 'INV-2026-0010', 'project' => 'Sistem Informasi D2', 'date' => '20 Okt 2026', 'status' => 'Diproses', 'amount' => 'Rp 45.750.000']] as $invoice)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-800">{{ $invoice['id'] }}</td><td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $invoice['project'] }}</td><td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $invoice['date'] }}</td>
                                <td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $invoice['status'] === 'Dibayar' ? 'bg-emerald-50 text-emerald-600' : ($invoice['status'] === 'Diproses' ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600') }}">{{ $invoice['status'] }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-slate-800">{{ $invoice['amount'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex items-center justify-between">
                <div><h3 class="font-bold text-slate-950">Proyek aktif</h3><p class="mt-1 text-xs text-slate-500">Progress termin berjalan</p></div>
                <a href="#" class="text-xs font-bold text-indigo-600">Lihat semua</a>
            </div>
            <div class="mt-6 space-y-5">
                @foreach ([['name' => 'Renovasi Gedung A', 'value' => '75%', 'bar' => 'bg-indigo-500'], ['name' => 'Pembangunan Jalan Tol', 'value' => '52%', 'bar' => 'bg-emerald-500'], ['name' => 'Sistem Informasi D2', 'value' => '32%', 'bar' => 'bg-amber-500']] as $project)
                    <div><div class="mb-2 flex justify-between text-xs"><span class="font-semibold text-slate-700">{{ $project['name'] }}</span><span class="font-bold text-slate-500">{{ $project['value'] }}</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $project['bar'] }}" style="width: {{ $project['value'] }}"></div></div></div>
                @endforeach
            </div>
            <div class="mt-7 rounded-xl bg-indigo-50 p-4"><p class="text-xs font-bold text-indigo-800">Jangan lewatkan termin berikutnya</p><p class="mt-1 text-xs leading-5 text-indigo-600">Ada 3 termin yang jatuh tempo dalam 7 hari ke depan.</p></div>
        </section>
    </div>
@endsection
