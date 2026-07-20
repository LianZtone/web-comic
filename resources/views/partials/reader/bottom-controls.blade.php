<div class="fixed bottom-5 right-4 z-50 flex flex-col gap-3 transition-all duration-300 ease-out sm:right-5" data-reader-chrome
    data-reader-chrome-type="bottom">
    <button type="button"
        class="btn btn-circle btn-ghost btn-sm border border-base-300/70 bg-base-100 shadow-lg"
        data-reader-scroll-control="top" data-reader-scroll="top" aria-label="Scroll ke atas">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 15 7-7 7 7" />
        </svg>
    </button>
    <button type="button"
        class="btn btn-circle btn-ghost btn-sm border border-base-300/70 bg-base-100 shadow-lg"
        data-reader-scroll-control="bottom" data-reader-scroll="bottom" aria-label="Scroll ke bawah">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
        </svg>
    </button>
</div>

<div class="fixed inset-x-0 bottom-5 z-40 flex justify-center px-16 transition-all duration-300 ease-out sm:px-20"
    data-reader-chrome data-reader-chrome-type="bottom">
    <div class="flex items-center gap-3 rounded-full border border-base-300/70 bg-base-100 px-4 py-3 shadow-xl">
        @if ($previousChapter)
            <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $previousChapter['number']]) }}"
                class="btn btn-circle btn-ghost btn-sm" aria-label="Chapter sebelumnya">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        @endif

        <button type="button" class="btn btn-circle btn-ghost btn-sm" data-reader-modal-open
            aria-label="Buka daftar chapter">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
            </svg>
        </button>

        <a href="{{ route('home') }}" class="btn btn-ghost btn-circle btn-sm" aria-label="Beranda">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1v-10.5Z" />
            </svg>
        </a>

        @if ($nextChapter)
            <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $nextChapter['number']]) }}"
                class="btn btn-circle btn-primary btn-sm" aria-label="Chapter berikutnya">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @endif
    </div>
</div>
