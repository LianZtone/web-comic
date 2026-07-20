@extends('layouts.admin', [
    'title' => 'Komentar Seri | Velmics',
    'description' => 'Moderasi feedback dan ulasan seri di Velmics.',
])

@section('admin_content')
    @include('admin.comic-comments.partials.header')
    @include('admin.comic-comments.partials.stats')
    @include('admin.comic-comments.partials.filters')

    <form method="POST" action="{{ route('admin.comic-comments.bulk') }}" class="space-y-4">
        @csrf

        <section class="rounded-box border border-base-300/70 bg-base-100 p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-3 rounded-2xl border border-base-300/70 px-3 py-2">
                        <input type="checkbox" class="checkbox checkbox-sm" data-bulk-toggle>
                        <span class="text-sm text-base-content/70">Pilih semua di halaman ini</span>
                    </label>
                    <div class="text-sm text-base-content/55">Aksi massal untuk komentar seri yang dicentang.</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="action" value="show" class="btn btn-ghost rounded-2xl border border-base-300/70">Tampilkan</button>
                    <button type="submit" name="action" value="hide" class="btn btn-outline rounded-2xl">Sembunyikan</button>
                    <button type="submit" name="action" value="delete" class="btn btn-error rounded-2xl" onclick="return confirm('Hapus semua komentar seri yang dicentang?')">Hapus</button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-box border border-base-300/70 bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                <thead>
                    <tr>
                        <th class="w-12">Pilih</th>
                        <th>Pengguna</th>
                        <th>Ulasan</th>
                        <th>Komik</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Like</th>
                        <th>Waktu</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        @include('admin.comic-comments.partials.comment-row')
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center">
                                <div class="text-2xl font-semibold">{{ $filters['q'] !== '' || $filters['visibility'] !== '' ? 'Tidak ada komentar seri yang cocok.' : 'Belum ada komentar seri masuk.' }}</div>
                                <div class="mt-2 text-base-content/65">{{ $filters['q'] !== '' || $filters['visibility'] !== '' ? 'Coba ubah filter pencarian atau status tampil.' : 'Ulasan seri dari halaman detail komik akan muncul di sini.' }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </section>
    </form>

    @if ($comments->hasPages())
        <section class="pt-2">
            {{ $comments->onEachSide(1)->links() }}
        </section>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-bulk-toggle]');
            const items = Array.from(document.querySelectorAll('[data-bulk-item]'));

            if (!toggle || items.length === 0) {
                return;
            }

            const syncToggle = () => {
                const checkedCount = items.filter((item) => item.checked).length;
                toggle.checked = checkedCount === items.length;
                toggle.indeterminate = checkedCount > 0 && checkedCount < items.length;
            };

            toggle.addEventListener('change', () => {
                items.forEach((item) => {
                    item.checked = toggle.checked;
                });
                syncToggle();
            });

            items.forEach((item) => {
                item.addEventListener('change', syncToggle);
            });

            syncToggle();
        });
    </script>
@endsection
