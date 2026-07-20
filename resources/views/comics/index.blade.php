@extends('layouts.app', [
    'title' => 'Velmics | Katalog',
    'description' => 'Jelajahi katalog komik Velmics melalui pencarian dan filter yang ringkas.',
])

@section('content')
    @php
        $selectedGenres = collect($filters['genres'] ?? []);
    @endphp

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] ">
        <div class="rounded-[2rem] border border-base-300/70 bg-base-100 p-6 shadow-lg sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary">Katalog</span>
                <span class="badge badge-outline border-base-300/70">Ringkas</span>
                <span class="badge badge-outline border-base-300/70">Siap dibaca</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-semibold leading-tight sm:text-4xl">Cari seri, pilih chapter, lalu langsung baca.</h1>

        </div>

        <div class="rounded-[2rem] border border-base-300/70 bg-base-100 p-5 shadow-sm">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Trending</div>
            <h2 class="mt-3 text-2xl font-semibold">{{ $featured['title'] }}</h2>
            <p class="mt-2 line-clamp-2 text-sm text-base-content/65">{{ $featured['tagline'] }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <span class="badge badge-primary">{{ $featured['status'] }}</span>
                <span class="badge badge-outline border-base-300/70">{{ $featured['comic_type'] }}</span>
                <span class="badge badge-outline border-base-300/70">{{ number_format($featured['rating_average'], 1) }}/5</span>
                <span class="badge badge-outline border-base-300/70">{{ $featured['chapter_total'] }} chapter</span>
                <span class="badge badge-outline border-base-300/70">{{ $featured['views_label'] }} views</span>
            </div>
            <div class="mt-5 flex gap-2">
                <a href="{{ route('comics.show', $featured['slug']) }}" class="btn btn-primary btn-sm rounded-2xl">Detail</a>
                <a href="{{ route('chapters.show', ['slug' => $featured['slug'], 'chapter' => $featured['first_chapter']['number']]) }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Baca</a>
            </div>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)]" style="content-visibility: auto; contain-intrinsic-size: 1800px; mb-10">
        <aside class="hidden h-fit lg:sticky lg:top-24 lg:block mb-10">
            <form method="GET" action="{{ route('comics.index') }}" class="rounded-[2rem] border border-base-300/70 bg-base-100/70 p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Filter</div>
                    </div>
                    <a href="{{ route('comics.index') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Reset</a>
                </div>

                <div class="mt-2 space-y-5 mb-4">
                    @include('partials.comics.catalog-filter-fields', [
                        'genreListClass' => 'max-h-80 space-y-1 overflow-y-auto pr-1',
                        'typeFieldClass' => 'w-full',
                    ])
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                        Cari
                    </button>
                </div>
            </form>
        </aside>

        <div class="space-y-4 lg:space-y-6 mb-10">
            <div class="rounded-[2rem] border border-base-300/70 bg-base-100/60 p-5 shadow-sm lg:hidden">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Mobile Filter</div>
                        <h2 class="mt-2 text-2xl font-semibold">Cari cepat</h2>
                    </div>
                    <button type="button" class="btn btn-primary rounded-2xl" onclick="document.getElementById('catalog-filter-sheet').showModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4" />
                        </svg>
                        Filter
                    </button>
                </div>

                @include('partials.comics.catalog-active-filters', ['class' => 'mt-4 flex flex-wrap gap-2 text-xs'])
            </div>

            <dialog id="catalog-filter-sheet" class="modal modal-bottom lg:hidden">
                <div class="modal-box max-h-[88vh] rounded-t-[2rem] border border-base-300/70 bg-base-100 p-0">
                    <form method="GET" action="{{ route('comics.index') }}" class="flex h-full max-h-[88vh] flex-col">
                        <div class="flex items-center justify-between border-b border-base-300/70 px-5 py-4">
                            <div>
                                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Filter</div>
                                <h3 class="mt-1 text-xl font-semibold">Atur pencarian</h3>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('catalog-filter-sheet').close()" aria-label="Tutup filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                            @include('partials.comics.catalog-filter-fields', [
                                'genreListClass' => 'max-h-64 space-y-1 overflow-y-auto pr-1',
                            ])
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-base-300/70 px-5 py-4">
                            <a href="{{ route('comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Reset</a>
                            <button type="submit" class="btn btn-primary rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button aria-label="Tutup filter">close</button>
                </form>
            </dialog>

            {{-- <div class="rounded-[2rem] border border-base-300/70 bg-base-100/60 p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Hasil pencarian</div>
                        <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $total }} judul ditemukan</h2>
                    </div>
                    @include('partials.comics.catalog-active-filters')
                </div>
            </div> --}}

            @if ($comics->isEmpty())
                <div class="hero rounded-[2rem] border border-dashed border-base-300/70 mb-8 bg-base-100/50 py-16 shadow-sm">
                    <div class="hero-content text-center">
                        <div class="max-w-lg space-y-4">
                            <h3 class="text-3xl font-semibold">Belum ada hasil yang cocok.</h3>
                            <p class="text-base-content/70">Coba kata kunci lain atau hapus beberapa filter aktif.</p>
                            <a href="{{ route('comics.index') }}" class="btn btn-primary rounded-2xl">Hapus Filter</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-6 mb-8">
                    @foreach ($comics as $comic)
                        @include('partials.comics.catalog-card')
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
