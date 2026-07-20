<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Total komik</div>
        <div class="stat-value text-3xl">{{ $stats['total_comics'] }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Total chapter</div>
        <div class="stat-value text-3xl">{{ $stats['total_chapters'] }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Featured</div>
        <div class="stat-value text-3xl">{{ $stats['featured_total'] }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Komentar</div>
        <div class="stat-value text-3xl">{{ $stats['total_comments'] }}</div>
        <div class="stat-desc">{{ $commentsReady ? $stats['hidden_comments'].' tersembunyi dari publik.' : 'Aktif setelah tabel komentar siap.' }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Reactions</div>
        <div class="stat-value text-3xl">{{ $stats['total_reactions'] }}</div>
        <div class="stat-desc">{{ $reactionsReady ? 'Total respons pembaca lintas chapter.' : 'Aktif setelah tabel reaction siap.' }}</div>
    </div>
</section>
