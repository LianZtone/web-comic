@extends('layouts.app', [
    'title' => 'Velmics | Library',
    'description' => 'Kumpulkan history, bookmark, readlist, dan pintasan ke catalog dalam satu rak baca.',
])

@section('content')
    <section class="space-y-5" data-library-shell data-library-active-tab="history">
        <article class="rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-lg sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary">Library</span>
                <span class="badge badge-outline border-base-300/70">Reading hub</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-semibold leading-tight sm:text-4xl">
                Satu halaman buat merapikan jejak baca, daftar simpan, dan pintasan ke catalog.
            </h1>
            <p class="mt-3 max-w-2xl text-base-content/70">
                History dipakai buat lanjut cepat, bookmark buat judul yang ingin disimpan, readlist buat antrean baca, dan catalog tetap jadi gerbang ke semua seri.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" class="btn btn-primary btn-sm rounded-2xl" data-library-tab="history">
                    History
                    <span class="badge badge-sm border-0 bg-base-100/80 text-base-content" data-library-count="history">0 tersimpan</span>
                </button>
                <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70" data-library-tab="bookmarks">
                    Bookmark
                    <span class="badge badge-sm border-0 bg-base-200 text-base-content" data-library-count="bookmarks">0 tersimpan</span>
                </button>
                <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70" data-library-tab="readlist">
                    Readlist
                    <span class="badge badge-sm border-0 bg-base-200 text-base-content" data-library-count="readlist">0 tersimpan</span>
                </button>
                <button type="button" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70" data-library-tab="catalog">
                    Catalog
                </button>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-base-300/70 bg-base-200/45 p-4 text-sm text-base-content/65">
                Rak baca disimpan lokal di browser ini dan dipisah per akun yang login, jadi history, bookmark, dan
                readlist tidak tercampur antar user.
            </div>
        </article>

        <section class="space-y-4" data-library-panel="history">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Riwayat baca</div>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h2 class="text-3xl font-semibold sm:text-4xl">History</h2>
                        <span class="badge badge-outline border-base-300/70" data-library-count="history">0 tersimpan</span>
                    </div>
                </div>
                <a href="{{ route('library.history') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Buka halaman history</a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 w-full" data-library-preview="history"></div>
            <div class="rounded-[1.75rem] border border-dashed border-base-300/70 bg-base-100/55 p-6 text-sm text-base-content/60"
                data-library-empty="history">
                History akan muncul setelah ada chapter yang dibuka.
            </div>
        </section>

        <section class="hidden space-y-4" data-library-panel="bookmarks">
            <div>
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Simpan judul favorit</div>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-semibold sm:text-4xl">Bookmark</h2>
                    <span class="badge badge-outline border-base-300/70" data-library-count="bookmarks">0 tersimpan</span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-library-preview="bookmarks"></div>
            <div class="rounded-[1.75rem] border border-dashed border-base-300/70 bg-base-100/55 p-6 text-sm text-base-content/60"
                data-library-empty="bookmarks">
                Belum ada bookmark. Simpan komik dari halaman detail untuk mengisinya.
            </div>
            <div class="flex justify-end">
                <a href="{{ route('library.bookmarks') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Buka halaman bookmark</a>
            </div>
        </section>

        <section class="hidden space-y-4" data-library-panel="readlist">
            <div>
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Antrean baca</div>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-semibold sm:text-4xl">Readlist</h2>
                    <span class="badge badge-outline border-base-300/70" data-library-count="readlist">0 tersimpan</span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-library-preview="readlist"></div>
            <div class="rounded-[1.75rem] border border-dashed border-base-300/70 bg-base-100/55 p-6 text-sm text-base-content/60"
                data-library-empty="readlist">
                Readlist masih kosong. Tambahkan judul dari halaman komik untuk bikin antrean baca.
            </div>
            <div class="flex justify-end">
                <a href="{{ route('library.readlist') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Buka halaman readlist</a>
            </div>
        </section>

        <section class="hidden space-y-4 mb-5" data-library-panel="catalog">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Pintasan koleksi</div>
                    <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">Catalog</h2>
                </div>
                <a href="{{ route('comics.index') }}" class="btn btn-primary btn-sm rounded-2xl">Lihat semua</a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ($catalogPreview as $comic)
                    @include('partials.home.shelf-card', ['comic' => $comic])
                @endforeach
            </div>
        </section>
    </section>
@endsection
