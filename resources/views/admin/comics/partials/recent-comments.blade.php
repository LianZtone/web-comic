<div class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Aktivitas pembaca</div>
    <h2 class="mt-2 text-2xl font-semibold">Komentar terbaru</h2>

    <div class="mt-4 space-y-3">
        @forelse ($recentComments as $comment)
            <div class="rounded-box border border-base-300/70 bg-base-100 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold">{{ $comment->display_name }}</div>
                    <div class="text-xs text-base-content/45">{{ optional($comment->created_at)->diffForHumans() ?? 'baru saja' }}</div>
                </div>
                <div class="mt-2 text-sm text-base-content/60">
                    {{ $comment->chapter?->comic?->title }} · Chapter {{ str_pad((string) ($comment->chapter?->number ?? 0), 2, '0', STR_PAD_LEFT) }}
                </div>
                <p class="mt-2 line-clamp-3 text-sm text-base-content/80">{{ $comment->body }}</p>
            </div>
        @empty
            <div class="rounded-box border border-dashed border-base-300/70 bg-base-100 p-4 text-sm text-base-content/60">
                {{ $commentsReady ? 'Belum ada komentar dari pembaca.' : 'Tabel komentar belum aktif.' }}
            </div>
        @endforelse
    </div>
</div>
