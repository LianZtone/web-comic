@extends('layouts.admin', [
    'title' => 'Edit Komik | Velmics',
    'description' => 'Perbarui data seri dan chapter di backend Velmics.',
])

@section('admin_content')
    <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
        {{-- tombol kembali ke kelola chapter --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali ke Dashboard Komik</a>
        </div>
        <div class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
            <div class="mb-6">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Admin</div>
                <h1 class="mt-2 text-3xl font-semibold">Edit  {{ $comic->title }}</h1>
            </div>

            <form action="{{ route('admin.comics.update', $comic) }}" method="POST" enctype="multipart/form-data">
                @include('admin.comics._form')
            </form>
        </div>

        <aside class="space-y-4">
            <div class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Ringkasan</div>
                <h2 class="mt-2 text-xl font-semibold">{{ $comic->title }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="badge badge-primary">{{ $comic->status }}</span>
                    <span class="badge badge-outline">{{ $comic->comic_type ?? 'Manhwa' }}</span>
                    <span class="badge badge-outline">{{ $comic->source_type ?? 'Project' }}</span>
                    <span class="badge badge-outline">{{ $comic->chapters->count() }} chapter</span>
                    @if ($comic->is_featured)
                        <span class="badge badge-secondary">Featured</span>
                    @endif
                </div>
                <div class="mt-4">
                    <a href="{{ route('comics.show', $comic->slug) }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Preview Halaman Publik</a>
                </div>
            </div>

            <div class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Chapter</div>
                        <h2 class="mt-2 text-2xl font-semibold">Daftar chapter</h2>
                    </div>
                    <a href="{{ route('admin.chapters.create', $comic) }}" class="btn btn-primary btn-sm rounded-2xl">Tambah</a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($comic->chapters as $chapter)
                        <article class="rounded-box border border-base-300/70 bg-base-100 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="text-sm font-semibold">Chapter {{ str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge badge-sm {{ $chapter->is_published ? 'badge-success' : 'badge-warning' }}">
                                            {{ $chapter->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-sm text-base-content/60">{{ $chapter->title }}</div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.chapters.publication', $chapter) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_published" value="{{ $chapter->is_published ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-outline btn-xs rounded-xl">
                                            {{ $chapter->is_published ? 'Draft' : 'Publish' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.chapters.edit', [$comic, $chapter]) }}" class="btn btn-ghost btn-xs rounded-xl border border-base-300/70">Edit</a>
                                    <form action="{{ route('admin.chapters.destroy', [$comic, $chapter]) }}" method="POST" onsubmit="return confirm('Hapus chapter ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs rounded-xl">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-box border border-dashed border-base-300/70 bg-base-100 p-5 text-sm text-base-content/60">
                            Belum ada chapter untuk seri ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </section>
@endsection
