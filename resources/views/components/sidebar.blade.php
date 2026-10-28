@php
    $navigation = [
        [
            'label' => 'Overview',
            'route' => 'dashboard',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z"/>',
        ],
        [
            'label' => 'Proyek',
            'route' => 'projects',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5v11.25H3.75V7.5Zm3-3h4.5l1.5 3h7.5"/>',
        ],
        [
            'label' => 'Kontrak',
            'route' => 'contracts',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h7.879L19.5 7.871V20.25h-12V3.75Zm7.5.75v4.5h4.5M10.5 12h6m-6 3h6"/>',
        ],
        [
            'label' => 'Termin',
            'route' => 'termins',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A1.75 1.75 0 0 1 19 5.5v13a1.75 1.75 0 0 1-1.75 1.75H6.75A1.75 1.75 0 0 1 5 18.5v-13a1.75 1.75 0 0 1 1.75-1.75ZM8.5 8h7m-7 3.5h7m-7 3.5h4"/>',
        ],
        [
            'label' => 'Invoice',
            'route' => 'invoices',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A1.75 1.75 0 0 1 18.25 5.5v15l-3.25-1.75-3.25 1.75-3.25-1.75-3.25 1.75v-15A1.75 1.75 0 0 1 7.5 3.75ZM9 9h6m-6 3.5h4.5"/>',
        ],
    ];
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-7">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5A2.5 2.5 0 0 1 7.5 3h9A2.5 2.5 0 0 1 19 5.5v13a2.5 2.5 0 0 1-3.75 2.165L12 18.75l-3.25 1.915A2.5 2.5 0 0 1 5 18.5v-13Z"/>
                <path stroke-linecap="round" d="M9 8h6M9 11.5h6"/>
            </svg>
        </div>
        <div>
            <p class="text-lg font-extrabold tracking-tight text-slate-950">Invoice<span class="text-indigo-600">Ku</span></p>
            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Modul D2</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 px-4 py-7">
        <p class="mb-3 px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Menu utama</p>
        @foreach ($navigation as $item)
            @php
                $isActive = request()->routeIs($item['route']) || ($item['route'] === 'invoices' && request()->is('invoices*'));
            @endphp
            <a
                href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                class="{{ $isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }} group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition"
            >
                <svg class="{{ $isActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }} h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    {!! $item['icon'] !!}
                </svg>
                {{ $item['label'] }}
                @if ($item['label'] === 'Invoice')
                    <span class="ml-auto rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700">12</span>
                @endif
            </a>
        @endforeach

        <p class="mb-3 mt-9 px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Lainnya</p>
        <a href="#" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
            <svg class="h-5 w-5 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.4 15 .05.05a1.75 1.75 0 0 1-2.475 2.475l-.05-.05a1.75 1.75 0 0 0-2.985 1.238v.142a1.75 1.75 0 0 1-3.5 0v-.071a1.75 1.75 0 0 0-2.985-1.238l-.05.05a1.75 1.75 0 1 1-2.475-2.475l.05-.05A1.75 1.75 0 0 0 5.742 12H5.6a1.75 1.75 0 0 1 0-3.5h.071a1.75 1.75 0 0 0 1.238-2.985l-.05-.05a1.75 1.75 0 1 1 2.475-2.475l.05.05A1.75 1.75 0 0 0 12.32 4.28v-.142a1.75 1.75 0 0 1 3.5 0v.071a1.75 1.75 0 0 0 2.985 1.238l.05-.05a1.75 1.75 0 1 1 2.475 2.475l-.05.05A1.75 1.75 0 0 0 20.258 11h.142a1.75 1.75 0 0 1 0 3.5h-.071A1.75 1.75 0 0 0 19.4 15Z"/>
            </svg>
            Pengaturan
        </a>
        <a href="#" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
            <svg class="h-5 w-5 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M9.75 9.5a2.3 2.3 0 1 1 3.95 1.6c-.9.85-1.7 1.13-1.7 2.4m.02 2.75v.01"/>
            </svg>
            Bantuan
        </a>
    </nav>

    <div class="m-4 rounded-2xl bg-indigo-600 p-4 text-white shadow-lg shadow-indigo-100">
        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 3v18m6-15.5H9.75a2.75 2.75 0 1 0 0 5.5h4.5a2.75 2.75 0 1 1 0 5.5H6"/></svg>
        </div>
        <p class="text-sm font-bold">Butuh bantuan?</p>
        <p class="mt-1 text-xs leading-5 text-indigo-100">Hubungi tim support kami jika mengalami kendala.</p>
        <a href="#" class="mt-3 inline-flex text-xs font-bold text-white underline underline-offset-2">Hubungi support</a>
    </div>
</div>
