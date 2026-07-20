@extends('layouts.app', [
    'title' => 'Velmics | Beranda',
    'description' => 'Baca rilisan terbaru, lanjutkan chapter terakhir, dan temukan seri baru dengan cepat.',
])

@section('content')
    <section class="space-y-5">
        <article class="relative overflow-hidden rounded-[2rem] border border-base-300/50 bg-base-100/70 shadow-xl">
            <img src="{{ $featured['banner'] }}" alt="{{ $featured['title'] }} banner"
                class="h-[340px] w-full object-cover opacity-80 sm:h-[420px]" fetchpriority="high" decoding="async">
            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/65 to-transparent"></div>
            <div class="absolute inset-0 flex items-end p-5 sm:p-8">
                <div class="max-w-2xl">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-black/35 px-3 py-1 text-sm font-semibold text-white">
                            <span class="text-warning">★</span>
                            {{ number_format($featured['rating_average'], 1) }}
                        </span>
                        <span class="badge badge-error border-0 text-white">{{ $featured['genres'][0] }}</span>
                        <span class="badge badge-outline border-white/20 text-white">{{ $featured['status'] }}</span>
                    </div>

                    <h1 class="max-w-xl text-3xl font-bold leading-tight text-white sm:text-5xl">{{ $featured['title'] }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-white/75 sm:text-base">{{ $featured['summary'] }}</p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('chapters.show', ['slug' => $featured['slug'], 'chapter' => $featured['first_chapter']['number']]) }}"
                            class="btn btn-primary rounded-xl btn-sm sm:btn-md">
                            Baca Sekarang
                        </a>
                        <a href="{{ route('comics.show', $featured['slug']) }}"
                            class="btn btn-ghost btn-sm border rounded-xl border-white/15 bg-black/20 text-white hover:bg-black/35 sm:btn-md">
                            Detail
                        </a>
                    </div>
                </div>
                <div class="absolute right-5 top-5">
                    <div class="rounded-2xl border border-white/15 bg-black/35 px-3 py-2 text-white backdrop-blur-sm">
                        <div class="text-[10px] uppercase tracking-[0.2em] text-white/60">Waktu Sekarang</div>
                        <div class="mt-2 grid auto-cols-max grid-flow-col gap-2 text-center" data-live-clock>
                            <div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
                                <span class="countdown font-mono text-2xl sm:text-3xl">
                                    <span data-live-clock-hours style="--value:0;" aria-live="polite" aria-label="00">00</span>
                                </span>
                                <span class="text-[10px] uppercase tracking-[0.18em] text-neutral-content/70">jam</span>
                            </div>
                            <div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
                                <span class="countdown font-mono text-2xl sm:text-3xl">
                                    <span data-live-clock-minutes style="--value:0;" aria-live="polite" aria-label="00">00</span>
                                </span>
                                <span class="text-[10px] uppercase tracking-[0.18em] text-neutral-content/70">min</span>
                            </div>
                            <div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
                                <span class="countdown font-mono text-2xl sm:text-3xl">
                                    <span data-live-clock-seconds style="--value:0;" aria-live="polite" aria-label="00">00</span>
                                </span>
                                <span class="text-[10px] uppercase tracking-[0.18em] text-neutral-content/70">sec</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </article>

        
    </section>

    <section class="space-y-4" data-recommendation-section
        data-active-recommendation-filter="{{ collect($recommendationTypes)->contains('Manhwa') ? 'manhwa' : strtolower($recommendationTypes->first() ?? 'manhwa') }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Pilihan cepat</div>
                <h2 class="mt-1 text-2xl font-semibold sm:text-3xl">Rekomendasi</h2>
            </div>

            <div class="-mx-4 flex w-[calc(100%+2rem)] gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:w-auto sm:flex-wrap sm:overflow-visible sm:px-0"
                role="tablist" aria-label="Filter rekomendasi">
                @foreach ($recommendationTypes as $type)
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-recommendation-filter="{{ strtolower($type) }}" aria-pressed="false">
                        {{ $type }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6 mb-8" data-recommendation-container>
            @foreach ($recommendations as $index => $comic)
                @include('partials.home.recommendation-card', [
                    'comic' => $comic,
                    'featured' => $index === 0,
                ])
            @endforeach
        </div>

        <div class="{{ $recommendations->isEmpty() ? '' : 'hidden ' }}rounded-2xl border border-dashed border-base-300/70 bg-base-100 p-6 text-center text-sm text-base-content/60"
            data-recommendation-empty>
            Belum ada rekomendasi untuk type ini.
        </div>
    </section>

    @php
        $updateCards = $latestUpdates
            ->map(fn(array $item) => ['comic' => $item['comic']])
            ->concat(
                collect($shelves)->flatMap(
                    fn(array $shelf) => collect($shelf['items'])->map(fn(array $comic) => ['comic' => $comic]),
                ),
            )
            ->unique(fn(array $item) => $item['comic']['slug'])
            ->map(function (array $item) {
                $chapters = collect($item['comic']['chapters'])->sortByDesc('number')->take(2)->values()->all();

                return [
                    'comic' => $item['comic'],
                    'chapters' => $chapters,
                    'latest_chapter' => $chapters[0] ?? $item['comic']['latest_chapter'],
                    'source' => strtolower($item['comic']['source_type'] ?? 'Project'),
                ];
            })
            ->take(6)
            ->values();
    @endphp

    <section class="space-y-5" id="updates" data-update-section>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-3">
                <h2 class="text-3xl font-semibold sm:text-4xl">Update</h2>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="btn btn-primary btn-sm rounded-2xl" data-update-filter="project"
                        aria-pressed="true">Project</button>
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-update-filter="mirror" aria-pressed="false">Mirror</button>
                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70"
                        data-update-filter="all" aria-pressed="false">Semua</button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                    class="btn btn-square rounded-2xl border border-primary/30 bg-primary/15 text-primary"
                    data-update-layout="grid" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M4.5 4.5h6v6h-6v-6Zm9 0h6v6h-6v-6Zm-9 9h6v6h-6v-6Zm9 0h6v6h-6v-6Z" />
                    </svg>
                </button>
                <button type="button" class="btn btn-square rounded-2xl border border-base-300/70 bg-base-100"
                    data-update-layout="list" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 17h16" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6" data-update-container>
            @foreach ($updateCards as $item)
                @include('partials.home.update-card', ['item' => $item, 'source' => $item['source']])
            @endforeach
        </div>

        <div class="hidden rounded-2xl border border-dashed border-base-300/70 bg-base-100 p-6 text-center text-sm text-base-content/60"
            data-update-empty>
            Belum ada update untuk filter ini.
        </div>
    </section>
    {{-- jika user login maka tampilkan section history dan lanjut baca--}}
    @auth
        <section class="space-y-4" id="continue-reading" style="content-visibility: auto; contain-intrinsic-size: 420px;">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-2xl font-semibold sm:text-3xl">Lanjut baca</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-library-continue-list></div>
            <div class="rounded-[1.75rem] border border-dashed border-base-300/70 bg-base-100/55 p-6 text-sm text-base-content/60"
                data-library-continue-empty>
                History baca akun ini akan muncul setelah kamu membuka chapter.
            </div>
        </section>
    @endauth

    <section class="space-y-4" id="schedule" style="content-visibility: auto; contain-intrinsic-size: 360px;">
        <div class="card border border-base-300/70 bg-base-100/60 shadow-lg">
            <div class="card-body">
                <div class="mb-1">
                    <h2 class="text-2xl font-semibold sm:text-3xl">Jadwal</h2>
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($schedule as $item)
                        <div
                            class="flex items-center justify-between gap-3 rounded-2xl border border-base-300/70 bg-base-200/40 p-4">
                            <div class="min-w-0">
                                <div class="truncate font-semibold">{{ $item['title'] }}</div>
                                <div class="text-sm text-base-content/65">{{ $item['status'] }}</div>
                            </div>
                            <a href="{{ route('comics.show', $item['slug']) }}">
                                <span class="badge badge-xs">{{ $item['schedule'] }}</span>
                                {{-- <div class="badge badge-xs">{{ $item['schedule'] }} </div> --}}

                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @foreach (collect($shelves)->slice(1, 1) as $shelf)
        <section class="space-y-4" style="content-visibility: auto; contain-intrinsic-size: 760px;">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-base-content/55">{{ $shelf['title'] }}</p>
                    <h2 class="text-2xl font-semibold sm:text-3xl">{{ $shelf['caption'] }}</h2>
                </div>
                <a href="{{ route('comics.index') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5 mb-5">
                @foreach ($shelf['items'] as $comic)
                    @include('partials.home.shelf-card', ['comic' => $comic])
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
