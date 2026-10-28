<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'InvoiceKu') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            <div
                id="sidebar-backdrop"
                class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"
                aria-hidden="true"
            ></div>

            <aside
                id="app-sidebar"
                class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-300 lg:static lg:translate-x-0"
                aria-label="Navigasi utama"
            >
                @include('components.sidebar')
            </aside>

            <div class="flex min-h-screen min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white/90 px-5 backdrop-blur md:px-8">
                    <div class="flex items-center gap-4">
                        <button
                            id="sidebar-toggle"
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 lg:hidden"
                            aria-label="Buka menu navigasi"
                            aria-controls="app-sidebar"
                            aria-expanded="false"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <p class="hidden text-sm font-medium text-slate-500 sm:block">Selamat datang kembali,</p>
                            <h1 class="text-lg font-bold tracking-tight text-slate-950">{{ $pageHeading ?? 'Ringkasan' }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100" aria-label="Notifikasi">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0A2.004 2.004 0 0 1 7.5 15.1V11a4.5 4.5 0 1 1 9 0v4.1c0 .982-.71 1.82-1.643 1.982ZM10 19.5h4"/>
                            </svg>
                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-indigo-600 ring-2 ring-white"></span>
                        </button>
                        <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                            </div>
                            <div class="hidden leading-tight sm:block">
                                <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name ?? 'Admin D2' }}</p>
                                <p class="mt-1 text-xs text-slate-500">Administrator</p>
                            </div>
                            <svg class="hidden h-4 w-4 text-slate-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-5 py-7 md:px-8 lg:px-10">
                    <div class="mx-auto max-w-7xl">
                        @if (session('success'))
                            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                                <p class="font-semibold">Periksa kembali data yang dimasukkan.</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
