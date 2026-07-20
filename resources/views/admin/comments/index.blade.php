@extends('layouts.admin', [
    'title' => 'Moderasi Komentar | Velmics',
    'description' => 'Moderasi komentar pembaca di Velmics.',
])

@section('admin_content')
    @include('admin.comments.partials.header')
    @include('admin.comments.partials.stats')
    @include('admin.comments.partials.filters')

    <form method="POST" action="{{ route('admin.comments.bulk') }}" class="space-y-4">
        @csrf

        <section class="rounded-box border border-base-300/70 bg-base-100 p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-3 rounded-2xl border border-base-300/70 px-3 py-2">
                        <input type="checkbox" class="checkbox checkbox-sm" data-bulk-toggle>
                        <span class="text-sm text-base-content/70">Pilih semua di halaman ini</span>
                    </label>
                    <div class="text-sm text-base-content/55">Aksi massal untuk komentar yang dicentang.</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="action" value="show" class="btn btn-ghost rounded-2xl border border-base-300/70">Tampilkan</button>
                    <button type="submit" name="action" value="hide" class="btn btn-outline rounded-2xl">Sembunyikan</button>
                    <button type="submit" name="action" value="delete" class="btn btn-error rounded-2xl" onclick="return confirm('Hapus semua komentar yang dicentang?')">Hapus</button>
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
                        <th>Komentar</th>
                        <th>Chapter</th>
                        <th>Status</th>
                        <th>Like</th>
                        <th>Waktu</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        @include('admin.comments.partials.comment-row')
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center">
                                <div class="text-2xl font-semibold">{{ $filters['q'] !== '' || $filters['visibility'] !== '' ? 'Tidak ada komentar yang cocok.' : 'Belum ada komentar masuk.' }}</div>
                                <div class="mt-2 text-base-content/65">{{ $filters['q'] !== '' || $filters['visibility'] !== '' ? 'Coba ubah filter pencarian atau status tampil.' : 'Komentar pembaca akan muncul di sini begitu chapter mulai ramai.' }}</div>
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
