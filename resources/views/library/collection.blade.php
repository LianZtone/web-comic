@extends('layouts.app', [
    'title' => 'Velmics | ' . $collectionTitle,
    'description' => $collectionDescription,
])

@section('content')
    <section class="space-y-5">
        <article class="rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-lg sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary">Library</span>
                <span class="badge badge-outline border-base-300/70">{{ $collectionTitle }}</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-semibold leading-tight sm:text-4xl">
                {{ $collectionTitle }}
            </h1>
            <p class="mt-3 max-w-2xl text-base-content/70">
                {{ $collectionDescription }}
            </p>
        </article>

        <section class="space-y-4">
            @auth
                <div class="space-y-4" data-library-page="{{ $collectionKey }}">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Rak pribadi</div>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <h2 class="text-3xl font-semibold sm:text-4xl">{{ $collectionTitle }}</h2>
                                <span class="badge badge-outline border-base-300/70" data-library-page-count="{{ $collectionKey }}">0 tersimpan</span>
                            </div>
                        </div>
                        <a href="{{ route('library') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Kembali ke Library</a>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-library-page-list="{{ $collectionKey }}"></div>
                    <div class="rounded-[1.75rem] border border-dashed border-base-300/70 bg-base-100/55 p-6 text-sm text-base-content/60"
                        data-library-page-empty="{{ $collectionKey }}">
                        Belum ada item di {{ strtolower($collectionTitle) }}.
                    </div>
                </div>
            @else
                @include('partials.library.login-gate', ['label' => $collectionTitle])
            @endauth
        </section>
    </section>
@endsection
