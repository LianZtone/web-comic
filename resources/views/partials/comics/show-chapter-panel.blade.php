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
                    <span class="text-sm text-base-content/45">{{ $comic['chapter_total'] }} chapter</span>
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-chapter-sort-toggle>
                        <span data-chapter-sort-label>Urutan terbaru</span>
                    </button>
                </div>
            </div>

            <div class="space-y-3" data-chapter-list-shell data-chapter-page-size="{{ $chapterPageSize }}"
                data-chapter-visible-count="{{ min($chapters->count(), $chapterPageSize) }}"
                data-chapter-order="desc">
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
