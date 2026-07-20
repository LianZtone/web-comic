<section class="grid gap-4 md:grid-cols-3">
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Total komentar</div>
        <div class="stat-value text-3xl">{{ $stats['total'] }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Visible</div>
        <div class="stat-value text-3xl">{{ $stats['visible'] }}</div>
    </div>
    <div class="stat rounded-box border border-base-300/70 bg-base-100 shadow-sm">
        <div class="stat-title">Hidden</div>
        <div class="stat-value text-3xl">{{ $stats['hidden'] }}</div>
    </div>
</section>
