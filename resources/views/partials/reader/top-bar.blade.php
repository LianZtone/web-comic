<div class="sticky top-3 z-40 px-2 sm:px-3 transition-all duration-300 ease-out" data-reader-chrome data-reader-chrome-type="top">
    <div class="rounded-[1.75rem] border border-base-300/70 bg-base-100 px-4 py-3   shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('comics.show', $comic['slug']) }}" class="btn btn-ghost btn-circle btn-sm" aria-label="Kembali">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="min-w-0 text-center">
                <div class="truncate text-sm font-semibold">{{ $comic['title'] }}</div>
                <div class="text-sm text-primary">{{ $chapter['label'] }}</div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-circle btn-sm" aria-label="Beranda">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1v-10.5Z" />
                    </svg>
                </a>

                <button type="button" class="btn btn-ghost btn-circle btn-sm" data-reader-modal-open
                    aria-label="Buka daftar chapter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
