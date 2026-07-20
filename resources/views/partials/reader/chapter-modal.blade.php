<div class="pointer-events-none fixed inset-0 z-[80] hidden overflow-y-auto opacity-0 transition duration-200"
    data-reader-modal>
    <button type="button" class="absolute inset-0 block h-full w-full bg-black/50" data-reader-modal-close
        aria-label="Tutup daftar chapter"></button>

    <div class="relative flex min-h-full items-end justify-center p-4 sm:items-center">
        <div class="w-full max-w-2xl rounded-[1.75rem] border border-base-300/70 bg-base-100 p-4 shadow-2xl">
            <div class="flex items-center justify-between gap-3 border-b border-base-300/70 pb-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">{{ $comic['title'] }}</div>
                    <h2 class="mt-1 text-xl font-semibold">Daftar Chapter</h2>
                </div>
                <button type="button" class="btn btn-ghost btn-circle btn-sm" data-reader-modal-close
                    aria-label="Tutup daftar chapter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <label class="input input-bordered field-search-shell mt-4 flex w-full items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon opacity-70" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
                <input type="search" class="field-search-input grow" placeholder="Cari chapter" data-reader-search>
            </label>

            <div class="mt-4 max-h-[65vh] overflow-y-auto rounded-[1.25rem] bg-base-200/35 p-2">
                <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($chapters as $item)
                        <li data-reader-chapter-item
                            data-reader-search-text="{{ Str::lower($item['label'] . ' ' . $item['title']) }}">
                            <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $item['number']]) }}"
                                @class([
                                    'active block h-full rounded-xl bg-primary text-primary-content overflow-hidden' =>
                                        $item['number'] === $chapter['number'],
                                    'block h-full rounded-xl overflow-hidden' => $item['number'] !== $chapter['number'],
                                ])>
                                <div class="flex min-w-0 flex-nowrap items-center justify-between gap-3 p-4">
                                    <div class="min-w-0">
                                        <div class="truncate whitespace-nowrap font-semibold">{{ $item['label'] }}</div>
                                        <div class="truncate whitespace-nowrap text-xs opacity-70">{{ $item['title'] }}</div>
                                    </div>
                                    @if ($item['number'] === $chapter['number'])
                                        <span class="badge badge-sm shrink-0">Now</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="hidden px-3 py-6 text-center text-sm text-base-content/60" data-reader-search-empty>
                    Chapter yang kamu cari belum ada.
                </div>
            </div>
        </div>
    </div>
</div>
