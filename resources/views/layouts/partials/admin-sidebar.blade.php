@php
    $user = auth()->user();
@endphp

<aside class="flex min-h-full w-full flex-col overflow-x-visible">
    
    <div class="border-b border-base-300/70 p-4">
        <a href="{{ route('admin.comics.index') }}" class="flex items-center gap-3 is-drawer-close:justify-center" aria-label="Dashboard admin" title="Dashboard admin">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary text-primary-content font-display text-lg font-semibold">V</span>
            <span class="min-w-0 overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">
                <span class="block text-xs font-semibold uppercase tracking-[0.22em] text-base-content/45">Velmics Admin</span>
                <span class="block truncate text-base font-semibold">{{ $user->name }}</span>
            </span>
        </a>
    </div>

    <ul class="menu w-full gap-1 p-4">
        <li>
            <a href="{{ route('admin.comics.index') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.comics.index'),
            ]) aria-label="Dashboard Komik" title="Dashboard Komik">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-10h8V3h-8v8z" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Dashboard Komik</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.comics.create') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.comics.create'),
            ]) aria-label="Tambah Komik" title="Tambah Komik">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m7-7H5" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Tambah Komik</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.comments.index') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.comments.*'),
            ]) aria-label="Komentar Chapter" title="Komentar Chapter">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h8m-8 4h5m-7 7 2.5-2.5H19a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h1V21z" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Komentar Chapter</span>
                @if (($adminSidebarCounts['hiddenChapterComments'] ?? 0) > 0)
                    <span class="badge badge-warning badge-sm ml-auto is-drawer-close:hidden">{{ $adminSidebarCounts['hiddenChapterComments'] }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('admin.comic-comments.index') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.comic-comments.*'),
            ]) aria-label="Komentar Seri" title="Komentar Seri">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h6m-8 9 2.75-2.75H19A2 2 0 0 0 21 16V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2v3Z" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Komentar Seri</span>
                @if (($adminSidebarCounts['hiddenComicComments'] ?? 0) > 0)
                    <span class="badge badge-warning badge-sm ml-auto is-drawer-close:hidden">{{ $adminSidebarCounts['hiddenComicComments'] }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('admin.comics.curation') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.comics.curation*'),
            ]) aria-label="Explore Curation" title="Explore Curation">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h9" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Explore Curation</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.chapters.index') }}" @class([
                'gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2',
                'menu-active bg-primary text-primary-content' => request()->routeIs('admin.chapters.*'),
            ]) aria-label="Kelola Chapter" title="Kelola Chapter">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 4.5h9.75A2.25 2.25 0 0 1 18 6.75V19.5l-6-3-6 3V6.75A2.25 2.25 0 0 1 8.25 4.5H10.5" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Kelola Chapter</span>
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" class="gap-3 rounded-xl transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2" aria-label="Lihat Situs" title="Lihat Situs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Lihat Situs</span>
            </a>
        </li>
    </ul>
    
    <div class="mt-auto border-t border-base-300/70 p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost w-full justify-start rounded-xl border border-base-300/70 transition-all duration-200 ease-out is-drawer-close:justify-center is-drawer-close:px-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9m0 0 3-3m-3 3 3 3" />
                </svg>
                <span class="overflow-hidden whitespace-nowrap transition-all duration-200 ease-out is-drawer-open:max-w-48 is-drawer-open:opacity-100 is-drawer-close:max-w-0 is-drawer-close:opacity-0">Logout Admin</span>
            </button>
        </form>
    </div>
</aside>
