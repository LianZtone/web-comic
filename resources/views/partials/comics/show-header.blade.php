<div class="flex items-center justify-between gap-3">
    <a href="{{ route('comics.index') }}" aria-label="Kembali ke katalog"
        class="btn btn-ghost gap-2 rounded-2xl border border-base-300/70 bg-base-100/70 px-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="hidden sm:inline">Katalog</span>
    </a>

    <a href="{{ route('home') }}" class="btn btn-ghost btn-circle btn-sm border border-base-300/70 bg-base-100/70"
        aria-label="Beranda">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1v-10.5Z" />
        </svg>
    </a>
</div>
