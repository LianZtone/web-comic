@extends('layouts.app', [
    'title' => 'Velmics | Explore',
    'description' => 'Temukan update terbaru, rekomendasi editor, dan pilihan admin dalam satu halaman eksplorasi.',
])

@section('content')
    @php
        $headline = $updates->first();
    @endphp

    <section class="grid  gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <article class="rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-lg sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary">Explore</span>
                <span class="badge badge-outline border-base-300/70">Fresh grid</span>
                <span class="badge badge-outline border-base-300/70">Editor cut</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-semibold leading-tight sm:text-4xl">
                Update baru, rekomendasi pilihan, dan komik yang lagi layak dipantau.
            </h1>
            <p class="mt-3 max-w-2xl text-base-content/70">
                Halaman ini dibuat buat discovery cepat. Fokus utamanya cover, ritme update, dan pilihan judul yang lagi enak dibuka.
            </p>

            @if ($headline)
                <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-base-content/65">
                    <span class="rounded-full border border-base-300/70 bg-base-200/65 px-3 py-1.5">
                        {{ $headline['comic']['title'] }}
                    </span>
                    <span class="rounded-full border border-base-300/70 bg-base-200/65 px-3 py-1.5">
                        {{ $headline['chapter']['label'] }}
                    </span>
                    <span class="rounded-full border border-base-300/70 bg-base-200/65 px-3 py-1.5">
                        {{ $headline['chapter']['release_label'] }}
                    </span>
                </div>
            @endif
        </article>

        <aside class="rounded-[2rem] border border-base-300/70 bg-base-100/75 p-5 shadow-sm">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Ringkasan</div>
            <div class="mt-5 space-y-4">
                <div class="rounded-[1.4rem] border border-base-300/70 bg-base-200/55 p-4">
                    <div class="text-xs uppercase tracking-[0.22em] text-base-content/45">Update Grid</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $updates->count() }}</div>
                </div>
                <div class="rounded-[1.4rem] border border-base-300/70 bg-base-200/55 p-4">
                    <div class="text-xs uppercase tracking-[0.22em] text-base-content/45">Rekomendasi</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $recommendations->count() }}</div>
                </div>
                <div class="rounded-[1.4rem] border border-base-300/70 bg-base-200/55 p-4">
                    <div class="text-xs uppercase tracking-[0.22em] text-base-content/45">Pilihan Admin</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $adminPicks->count() }}</div>
                </div>
            </div>
        </aside>
    </section>

    <section class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Update terbaru</div>
                <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">Fresh updates</h2>
            </div>
            <a href="{{ route('comics.index') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Buka Catalog</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($updates as $item)
                @include('partials.explore.update-cover-card', ['item' => $item])
            @endforeach
        </div>
    </section>

    <section class="space-y-4">
        <div>
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Pilihan yang lagi cocok dibaca</div>
            <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">Rekomendasi</h2>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($recommendations as $index => $comic)
                @include('partials.home.recommendation-card', ['comic' => $comic, 'featured' => $index === 0])
            @endforeach
        </div>
    </section>

    <section class="space-y-4 mb-10">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Kurasi redaksi</div>
                <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">Pilihan admin</h2>
            </div>
            <a href="{{ route('library') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Buka Library</a>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($adminPicks as $comic)
                @include('partials.home.shelf-card', ['comic' => $comic])
            @endforeach
        </div>
    </section>
@endsection
