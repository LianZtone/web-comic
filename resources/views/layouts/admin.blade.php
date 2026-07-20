@extends('layouts.app', [
    'title' => $title ?? 'Admin | Velmics',
    'description' => $description ?? 'Panel admin Velmics.',
    'hideNavbar' => true,
    'hideFooter' => true,
    'hideAlerts' => true,
    'fullWidthLayout' => true,
])

@section('content')
    @php
        $adminPageTitle = trim(strtok($title ?? 'Admin Dashboard', '|'));
    @endphp

    <section class="drawer min-h-screen lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" data-admin-drawer />

        <div class="drawer-content flex min-h-screen flex-col ">
            <nav class="navbar border-b border-base-300/70 bg-base-100 px-4 lg:px-6">
                <div class="flex-1 gap-3">
                    <label for="admin-drawer" class="btn btn-square btn-ghost" aria-label="toggle sidebar" title="Toggle sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor">
                            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
                            <path d="M9 4v16"></path>
                            <path d="M14 10l2 2l-2 2"></path>
                        </svg>
                    </label>

                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Admin Panel</div>
                        <div class="truncate text-lg font-semibold">{{ $adminPageTitle }}</div>
                    </div>
                </div>
                

                <div class="flex items-center gap-2">
                    <label class="swap swap-rotate btn btn-ghost btn-square" aria-label="Toggle dark mode">
                        <input type="checkbox" hidden data-theme-checkbox aria-hidden="true" />
                        <svg class="swap-off h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                        </svg>
                        <svg class="swap-on h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                        </svg>
                        <span class="sr-only" data-theme-text>Aktifkan mode gelap</span>
                    </label>
                    <a href="{{ route('home') }}" class="btn btn-ghost btn-sm border border-base-300/70" aria-label="Lihat situs" title="Lihat situs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10" />
                        </svg>
                    </a>


                    <div class="hidden items-center gap-3 rounded-2xl border border-base-300/70 bg-base-100/70 px-3 py-2 sm:flex">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/15 text-sm font-semibold text-primary">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight">
                            <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-base-content/55">Administrator aktif</div>
                        </div>
                    </div>
                </div> 
            </nav>

            @include('layouts.partials.admin-alert-modal')

            <div class="flex-1 p-4 lg:p-6 z-0">
                <div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
                    @yield('admin_content')
                </div>
            </div>
        </div>

        <div class="drawer-side z-50">
            <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>

            <div class="min-h-full border-r border-base-300/70 bg-base-200 transition-[width] duration-300 ease-out is-drawer-open:w-72 is-drawer-close:w-24">
                @include('layouts.partials.admin-sidebar')
            </div>
        </div>
    </section>
@endsection
