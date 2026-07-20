@extends('layouts.admin', [
    'title' => 'Admin Chapter | Velmics',
    'description' => 'Kelola status terbit chapter, cek update terbaru, dan rapikan antrian rilis.',
])

@section('admin_content')
    <section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Editorial</div>
                <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">Kelola chapter</h1>
                <p class="mt-2 text-sm text-base-content/65">Pantau chapter lintas seri, cek status tayang, dan publish atau tarik chapter tanpa buka form satu-satu.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Dashboard Komik</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Total chapter</div>
            <div class="stat-value text-3xl">{{ $stats['total'] }}</div>
        </div>
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Published</div>
            <div class="stat-value text-3xl">{{ $stats['published'] }}</div>
        </div>
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Draft</div>
            <div class="stat-value text-3xl">{{ $stats['draft'] }}</div>
        </div>
    </section>

    <section class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
        <form action="{{ route('admin.chapters.index') }}" method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Cari chapter atau seri</span></div>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="input input-bordered field-shell w-full" placeholder="Judul chapter, nomor, judul komik, slug">
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Status tayang</span></div>
                <select name="publication" class="select select-bordered field-select">
                    <option value="">Semua status</option>
                    <option value="published" @selected($filters['publication'] === 'published')>Published</option>
                    <option value="draft" @selected($filters['publication'] === 'draft')>Draft</option>
                </select>
            </label>

            <div class="flex items-end gap-3">
                <button type="submit" class="btn btn-primary rounded-2xl">Terapkan</button>
                @if ($filters['q'] !== '' || $filters['publication'] !== '')
                    <a href="{{ route('admin.chapters.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Reset</a>
                @endif
            </div>
        </form>

        <div class="mt-4 text-sm text-base-content/60">
            Menampilkan {{ $chapters->count() }} chapter di halaman ini. Total hasil: {{ $chapters->total() }}.
        </div>
    </section>

    <section class="space-y-4">
        @forelse ($chapters as $chapter)
            <article class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge {{ $chapter->is_published ? 'badge-success' : 'badge-warning' }}">
                                {{ $chapter->is_published ? 'Published' : 'Draft' }}
                            </span>
                            <span class="badge badge-outline">Chapter {{ str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="badge badge-outline">{{ collect($chapter->pages ?? [])->count() }} halaman</span>
                            @if ($commentsReady)
                                <span class="badge badge-outline">{{ $chapter->comments_count }} komentar</span>
                            @endif
                        </div>

                        <h2 class="mt-3 text-xl font-semibold">{{ $chapter->comic?->title ?? 'Tanpa komik' }}</h2>
                        <div class="mt-1 text-sm text-base-content/65">{{ $chapter->title }}</div>

                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-base-content/50">
                            <span>Slug komik: {{ $chapter->comic?->slug ?? '-' }}</span>
                            <span>Diperbarui {{ optional($chapter->updated_at)->diffForHumans() ?? 'baru saja' }}</span>
                            @if ($chapter->release_label)
                                <span>Label rilis: {{ $chapter->release_label }}</span>
                            @endif
                        </div>

                        @if ($chapter->summary)
                            <p class="mt-3 line-clamp-2 text-sm text-base-content/75">{{ $chapter->summary }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 lg:max-w-[20rem] lg:justify-end">
                        @if ($chapter->comic)
                            <a href="{{ route('chapters.show', ['slug' => $chapter->comic->slug, 'chapter' => $chapter->number]) }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Preview</a>
                            <a href="{{ route('admin.chapters.edit', [$chapter->comic, $chapter]) }}" class="btn btn-outline btn-sm rounded-2xl">Edit</a>
                        @endif

                        <form method="POST" action="{{ route('admin.chapters.publication', $chapter) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_published" value="{{ $chapter->is_published ? 0 : 1 }}">
                            <button type="submit" class="btn btn-sm rounded-2xl {{ $chapter->is_published ? 'btn-warning' : 'btn-success' }}">
                                {{ $chapter->is_published ? 'Jadikan Draft' : 'Publish' }}
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-box border border-dashed border-base-300/70 bg-base-100 px-10 py-12 text-center shadow-sm">
                <h2 class="text-2xl font-semibold">{{ $filters['q'] !== '' || $filters['publication'] !== '' ? 'Tidak ada chapter yang cocok.' : 'Belum ada chapter tersimpan.' }}</h2>
                <p class="mt-2 text-base-content/65">{{ $filters['q'] !== '' || $filters['publication'] !== '' ? 'Coba ubah kata kunci atau status tayang yang dipakai.' : 'Tambahkan chapter dari halaman edit komik untuk memulai antrean rilis.' }}</p>
                <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost mt-5 rounded-2xl border border-base-300/70">Buka Dashboard Komik</a>
            </div>
        @endforelse
    </section>

    @if ($chapters->hasPages())
        <section class="pt-2">
            {{ $chapters->onEachSide(1)->links() }}
        </section>
    @endif
@endsection
