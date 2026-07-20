@extends('layouts.admin', [
    'title' => 'Explore Curation | Velmics',
    'description' => 'Atur komik yang masuk featured, rekomendasi, dan admin picks untuk halaman publik.',
])

@section('admin_content')
    <section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Kurasi</div>
                <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">Admin picks dan explore</h1>
                <p class="mt-2 text-sm text-base-content/65">Tentukan komik yang tampil di featured, rekomendasi explore, dan rak admin picks lengkap dengan urutan tampilnya.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Dashboard Komik</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Featured</div>
            <div class="stat-value text-3xl">{{ $stats['featured'] }}</div>
        </div>
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Rekomendasi</div>
            <div class="stat-value text-3xl">{{ $stats['recommended'] }}</div>
        </div>
        <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="stat-title">Admin picks</div>
            <div class="stat-value text-3xl">{{ $stats['admin_picks'] }}</div>
        </div>
    </section>

    @if ($setupRequired)
        <div role="alert" class="alert alert-warning border border-warning/30 bg-warning/10 text-warning-content shadow-sm">
            <span>Backend belum aktif penuh. Jalankan migrasi Laravel agar modul kurasi bisa dipakai.</span>
        </div>
    @elseif (! $curationReady)
        <div role="alert" class="alert alert-warning border border-warning/30 bg-warning/10 text-warning-content shadow-sm">
            <span>Kolom kurasi explore belum tersedia. Jalankan migrasi terbaru untuk mengaktifkan rekomendasi dan admin picks.</span>
        </div>
    @endif

    <section class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
        <form action="{{ route('admin.comics.curation') }}" method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Cari komik</span></div>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="input input-bordered field-shell w-full" placeholder="Judul, slug, author, artist">
            </label>

            <div class="flex items-end gap-3">
                <button type="submit" class="btn btn-primary rounded-2xl">Terapkan</button>
                @if ($filters['q'] !== '')
                    <a href="{{ route('admin.comics.curation') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Reset</a>
                @endif
            </div>
        </form>

        <div class="mt-4 text-sm text-base-content/60">
            Menampilkan {{ $comics->count() }} komik di halaman ini. Total hasil: {{ $comics->total() }}.
        </div>
    </section>

    <section class="overflow-hidden rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Komik</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Urutan</th>
                        <th>Rekomendasi</th>
                        <th>Urutan</th>
                        <th>Admin Pick</th>
                        <th>Urutan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comics as $comic)
                        <tr>
                            <td class="min-w-[18rem]">
                                <div class="font-semibold">{{ $comic->title }}</div>
                                <div class="text-xs text-base-content/55">{{ $comic->slug }} · {{ $comic->author }}</div>
                                <div class="mt-1 flex flex-wrap gap-1 text-xs text-base-content/45">
                                    <span>{{ $comic->chapters_count }} chapter</span>
                                    <span>•</span>
                                    <span>{{ $comic->comic_type ?? 'Manhwa' }}</span>
                                    <span>•</span>
                                    <span>{{ $comic->source_type ?? 'Project' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $comic->status }}</span>
                            </td>
                            <td>
                                <select name="is_featured" form="curation-{{ $comic->id }}" class="select select-bordered select-sm min-w-24">
                                    <option value="0" @selected(! $comic->is_featured)>Tidak</option>
                                    <option value="1" @selected($comic->is_featured)>Ya</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" name="sort_order" value="{{ $comic->sort_order ?? 0 }}" form="curation-{{ $comic->id }}" class="input input-bordered input-sm w-20">
                            </td>
                            <td>
                                <select name="is_recommended" form="curation-{{ $comic->id }}" class="select select-bordered select-sm min-w-24" @disabled(! $curationReady)>
                                    <option value="0" @selected(! $comic->is_recommended)>Tidak</option>
                                    <option value="1" @selected($comic->is_recommended)>Ya</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" name="recommended_order" value="{{ $comic->recommended_order ?? 0 }}" form="curation-{{ $comic->id }}" class="input input-bordered input-sm w-20" @disabled(! $curationReady)>
                            </td>
                            <td>
                                <select name="is_admin_pick" form="curation-{{ $comic->id }}" class="select select-bordered select-sm min-w-24" @disabled(! $curationReady)>
                                    <option value="0" @selected(! $comic->is_admin_pick)>Tidak</option>
                                    <option value="1" @selected($comic->is_admin_pick)>Ya</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" name="admin_pick_order" value="{{ $comic->admin_pick_order ?? 0 }}" form="curation-{{ $comic->id }}" class="input input-bordered input-sm w-20" @disabled(! $curationReady)>
                            </td>
                            <td class="min-w-[14rem]">
                                <div class="flex justify-end gap-2 flex-wrap">
                                    <form id="curation-{{ $comic->id }}" method="POST" action="{{ route('admin.comics.curation.update', $comic) }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <a href="{{ route('admin.comics.edit', $comic) }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Edit</a>
                                    <button type="submit" form="curation-{{ $comic->id }}" class="btn btn-primary btn-sm rounded-2xl" @disabled($setupRequired)>Simpan</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center">
                                <div class="text-xl font-semibold">{{ $filters['q'] !== '' ? 'Tidak ada komik yang cocok.' : 'Belum ada komik di database.' }}</div>
                                <div class="mt-2 text-sm text-base-content/60">{{ $filters['q'] !== '' ? 'Coba ubah kata kunci pencarian.' : 'Tambahkan komik dulu sebelum mulai kurasi explore.' }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($comics->hasPages())
        <section class="pt-2">
            {{ $comics->onEachSide(1)->links() }}
        </section>
    @endif
@endsection
