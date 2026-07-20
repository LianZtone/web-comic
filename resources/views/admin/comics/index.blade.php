@extends('layouts.admin', [
    'title' => 'Admin Komik | Velmics',
    'description' => 'Panel admin sederhana untuk mengelola komik dan chapter.',
])

@section('admin_content')
    @include('admin.comics.partials.header')
    @include('admin.comics.partials.stats')

    @if ($setupRequired)
        <div role="alert" class="alert alert-warning border border-warning/30 bg-warning/10 text-warning-content shadow-sm">
            <span>Backend belum aktif penuh. Jalankan migrasi Laravel terlebih dulu agar tabel `comics` dan `chapters` tersedia.</span>
        </div>
    @endif

    @include('admin.comics.partials.filters')

    <section class="grid gap-4 xl:grid-cols-2">
        @include('admin.comics.partials.recent-chapters')
        @include('admin.comics.partials.recent-comments')
    </section>

    <section class="overflow-hidden rounded-box border border-base-300/70 bg-base-100 shadow-sm z-0">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Komik</th>
                        <th>Status</th>
                        <th>Tipe / Sumber</th>
                        <th>Chapter</th>
                        <th>Author</th>
                        <th>Urutan</th>
                        <th>Kurasi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comics as $comic)
                        @include('admin.comics.partials.comic-row')
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center">
                                <div class="text-2xl font-semibold">{{ $filters['q'] !== '' || $filters['status'] !== '' ? 'Tidak ada hasil yang cocok.' : 'Belum ada komik di database.' }}</div>
                                <div class="mt-2 text-base-content/65">{{ $filters['q'] !== '' || $filters['status'] !== '' ? 'Coba ubah kata kunci atau status filter yang dipakai.' : 'Mulai dari satu seri dulu, lalu tambahkan chapter pertama.' }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if (method_exists($comics, 'hasPages') && $comics->hasPages())
        <section class="pt-2">
            {{ $comics->onEachSide(1)->links() }}
        </section>
    @endif
@endsection
