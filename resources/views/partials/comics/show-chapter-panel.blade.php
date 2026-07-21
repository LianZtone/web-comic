<section class="space-y-5">
    <div class="card border border-base-300/70 bg-base-100/60 shadow-lg rounded-[2rem]"
        style="content-visibility: auto; contain-intrinsic-size: 1200px;">
        <div class="card-body gap-5" data-chapter-panel>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Chapter</div>
                    <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">Daftar chapter</h2>
                    <p class="mt-1 text-sm text-base-content/55">{{ $comic['chapter_total'] }} chapter siap dibaca.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- <span class="text-sm text-base-content/45">{{ $comic['chapter_total'] }} chapter</span> --}}
                    {{-- view mode grid atau list --}}
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-chapter-view-toggle aria-pressed="false" aria-label="Ubah mode chapter">
                        <span class="sr-only" data-chapter-view-label>Mode grid</span>
                        <span data-chapter-view-icon-grid aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h8v8H3V3Zm10 0h8v8h-8V3ZM3 13h8v8H3v-8Zm10 0h8v8h-8v-8Z" />
                            </svg>
                        </span>
                        <span data-chapter-view-icon-list aria-hidden="true" class="hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01" />
                            </svg>
                        </span>
                    </button>

                    {{-- urutan chapter terbaru atau terlama --}}
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-chapter-sort-toggle>
                        <span data-chapter-sort-label>Urutan terbaru</span>
                    </button>
                </div>
            </div>

            {{-- daftar chapter --}}
            <div class="space-y-3" data-chapter-list-shell data-chapter-page-size="{{ $chapterPageSize }}"
                data-chapter-visible-count="{{ min($chapters->count(), $chapterPageSize) }}"
                data-chapter-order="desc" data-chapter-view-mode="grid" data-chapter-total="{{ $chapters->count() }}">
                @foreach ($chapters as $chapter)
                    @include('partials.comics.chapter-card', [
                        'chapter' => $chapter,
                        'comicSlug' => $comic['slug'],
                    ])
                @endforeach

                <div class="flex justify-center pt-2">
                    <button type="button" class="btn btn-ghost rounded-2xl border border-base-300/70 px-6"
                        data-chapter-load-more>
                        Lihat selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
