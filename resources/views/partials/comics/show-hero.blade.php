<article class="relative overflow-hidden rounded-[2rem] border border-base-300/70 bg-base-100/70 shadow-lg">
    <img src="{{ $comic['banner'] }}" alt="{{ $comic['title'] }} banner"
        class="h-[220px] w-full object-cover object-top opacity-75 sm:h-[300px] xl:h-[360px]" fetchpriority="high"
        decoding="async">
    <div class="absolute inset-0 bg-gradient-to-tr from-base-100 via-base-100/80 to-base-100/15"></div>

    {{-- comic info bisa di atas banner --}}
    <div class="relative z-10 p-5 sm:p-7">
        <div class="breadcrumbs hidden text-sm text-base-content/70 sm:block">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('comics.index') }}">Catalog</a></li>
                <li>{{ $comic['title'] }}</li>
            </ul>
        </div>

        <div class="mt-3 grid gap-5 lg:grid-cols-[180px_minmax(0,1fr)] xl:grid-cols-[200px_minmax(0,1fr)]">
            <div class="mx-auto w-full max-w-[180px] self-start lg:mx-0 lg:max-w-[200px]">
                <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover"
                    class="aspect-[4/5] w-full rounded-[1.75rem] border border-base-content/10 object-cover object-top shadow-xl"
                    decoding="async">
            </div>

            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-primary border-0 text-xs">{{ $comic['status'] }}</span>
                    <span class="badge badge-outline border-base-content/20 text-xs text-base-content">{{ $comic['comic_type'] }}</span>
                    <span class="badge badge-outline border-base-content/20 text-xs text-base-content">{{ $comic['source_type'] }}</span>
                    <span class="badge badge-outline gap-2 border-base-content/20 text-xs text-base-content">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span>{{ $comic['views_label'] }} views</span>
                        {{-- <span>{{ $comic['readers_label'] }} views</span> --}}
                    </span>
                </div>

                <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Rating</div>
                            <div class="mt-1 flex items-end gap-2">
                                <span class="text-lg font-semibold text-base-content">{{ number_format($comic['rating_average'], 1) }}</span>
                                <span class="pb-0.5 text-xs text-base-content/55">{{ $comic['rating_count_label'] }} vote</span>
                            </div>
                        </div>

                        <div>
                            @auth
                                @if ($ratingReady)
                                    <form method="POST" action="{{ route('comics.ratings.store', $comic['slug']) }}" class="flex flex-col gap-2 sm:items-end">
                                        @csrf
                                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Nilai komik ini</div>
                                        <div class="flex items-center gap-1">
                                            @for ($score = 1; $score <= 5; $score++)
                                                <button
                                                    type="submit"
                                                    name="score"
                                                    value="{{ $score }}"
                                                    class="group/button rounded-full p-1 transition duration-150 hover:-translate-y-0.5 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40"
                                                    aria-label="{{ $score }} bintang"
                                                    title="{{ $score }} bintang">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        @class([
                                                            'h-6 w-6 transition duration-150',
                                                            'fill-warning text-warning' => $score <= (int) $userRating,
                                                            'fill-warning/20 text-warning/60 group-hover/button:fill-warning group-hover/button:text-warning' => $score > (int) $userRating,
                                                        ])
                                                        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M11.48 3.5a.75.75 0 0 1 1.04 0l2.42 2.46 3.4.49a.75.75 0 0 1 .42 1.28l-2.46 2.4.58 3.39a.75.75 0 0 1-1.09.79L12 12.97l-3.79 1.99a.75.75 0 0 1-1.09-.79l.58-3.39-2.46-2.4a.75.75 0 0 1 .42-1.28l3.4-.49 2.42-2.46Z" />
                                                    </svg>
                                                </button>
                                            @endfor
                                            @if ($userRating)
                                                <span class="ml-2 text-xs text-base-content/60">{{ $userRating }}/5</span>
                                            @endif
                                        </div>
                                    </form>
                                @else
                                    <div class="text-xs text-base-content/60">Rating user akan aktif setelah tabel backend siap.</div>
                                @endif
                            @else
                                <div class="text-xs text-base-content/60">Login untuk kasih rating.</div>
                            @endauth
                        </div>
                    </div>
                </div>

                <div>
                    @if (!empty($comic['subtitle']))
                        <div class="text-sm uppercase tracking-[0.28em] text-base-content/55">{{ $comic['subtitle'] }}</div>
                    @endif
                    <h1 class="mt-2 max-w-4xl text-3xl font-bold leading-tight text-base-content sm:text-4xl xl:text-5xl">
                        {{ $comic['title'] }}
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-base-content/75 sm:text-base">
                        {{ $comic['summary'] }}
                    </p>
                </div>

                <div class="grid gap-2 sm:grid-cols-3 xl:grid-cols-3">
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Chapter</div>
                        <div class="mt-1 text-lg font-semibold text-base-content">{{ $comic['chapter_total'] }}</div>
                        <div class="text-xs text-base-content/55">{{ $comic['latest_chapter']['label'] }}</div>
                    </div>
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Bookmarks</div>
                        <div class="mt-1 text-lg font-semibold text-base-content">{{ $comic['bookmarks_label'] }}</div>
                    </div>
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Jadwal</div>
                        <div class="mt-1 truncate text-base font-semibold text-base-content">{{ $comic['schedule'] }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['latest_chapter']['number']]) }}"
                        class="btn btn-primary gap-2 rounded-2xl" data-library-continue>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path
                                d="M8 5.14v13.72a1 1 0 0 0 1.53.848l10.12-6.86a1 1 0 0 0 0-1.656L9.53 4.292A1 1 0 0 0 8 5.14Z" />
                        </svg>
                        <span data-library-continue-label>Lanjut Baca</span>
                    </a>
                    <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['first_chapter']['number']]) }}"
                        class="btn btn-ghost rounded-2xl border border-base-content/15 bg-base-100/40 text-base-content hover:bg-base-100/70">
                        Mulai dari Awal
                    </a>
                    @auth
                        <form method="POST" action="{{ route('comics.bookmarks.toggle', $comic['slug']) }}">
                            @csrf
                            <button type="submit"
                                class="btn gap-2 rounded-2xl border {{ $userBookmark ? 'btn-primary border-primary/30' : 'btn-ghost border-base-content/15 bg-base-100/40 text-base-content hover:bg-base-100/70' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16z" />
                                </svg>
                                <span>{{ $userBookmark ? 'Tersimpan' : 'Bookmark' }}</span>
                            </button>
                        </form>
                    @else
                        <button type="button"
                            class="btn btn-ghost gap-2 rounded-2xl border border-base-content/15 bg-base-100/40 text-base-content hover:bg-base-100/70"
                            data-library-toggle="bookmarks">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16z" />
                            </svg>
                            <span data-library-toggle-label>Bookmark</span>
                        </button>
                    @endauth
                    <button type="button"
                        class="btn btn-ghost gap-2 rounded-2xl border border-base-content/15 bg-base-100/40 text-base-content hover:bg-base-100/70"
                        data-library-toggle="readlist">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 6.75H7.75A2.75 2.75 0 0 0 5 9.5v8.75A1.75 1.75 0 0 0 6.75 20h9.5A1.75 1.75 0 0 0 18 18.25V9.5a2.75 2.75 0 0 0-2.75-2.75H14" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.75h6v4.5H9z" />
                        </svg>
                        <span data-library-toggle-label>Readlist</span>
                    </button>
                </div>

                <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                    <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Genre</div>
                    <div class="mt-2 flex flex-wrap gap-2 text-sm text-base-content/75">
                        @foreach ($comic['genres'] as $genre)
                            <span class="rounded-full border border-base-content/15 bg-base-100/45 px-3 py-1">{{ $genre }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Author</div>
                        <div class="mt-1 truncate text-base font-semibold text-base-content">{{ $comic['author'] }}</div>
                    </div>
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Artist</div>
                        <div class="mt-1 truncate text-base font-semibold text-base-content">{{ $comic['artist'] }}</div>
                    </div>
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Views</div>
                        <div class="mt-1 text-base font-semibold text-base-content">{{ $comic['views_label'] }}</div>
                    </div>
                    <div class="rounded-[1.15rem] border border-base-content/10 bg-base-100/65 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-base-content/45">Update</div>
                        <div class="mt-1 truncate text-base font-semibold text-base-content">{{ $comic['latest_chapter']['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
