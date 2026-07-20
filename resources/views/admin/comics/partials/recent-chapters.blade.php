<div class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Aktivitas chapter</div>
            <h2 class="mt-2 text-2xl font-semibold">Update terbaru</h2>
        </div>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Lihat Semua</a>
    </div>

    <div class="mt-4 space-y-3">
        @forelse ($recentChapters as $chapter)
            <div class="rounded-box border border-base-300/70 bg-base-100 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold">{{ $chapter->comic?->title ?? 'Tanpa komik' }}</div>
                        <div class="mt-1 text-sm text-base-content/70">Chapter {{ str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT) }} · {{ $chapter->title }}</div>
                        <div class="mt-1 text-xs text-base-content/45">Diperbarui {{ optional($chapter->updated_at)->diffForHumans() ?? 'baru saja' }}</div>
                    </div>
                    <a href="{{ route('admin.chapters.edit', [$chapter->comic, $chapter]) }}" class="btn btn-outline btn-sm rounded-2xl">Edit</a>
                </div>
            </div>
        @empty
            <div class="rounded-box border border-dashed border-base-300/70 bg-base-100 p-4 text-sm text-base-content/60">
                Belum ada chapter yang tersimpan di database.
            </div>
        @endforelse
    </div>
</div>
