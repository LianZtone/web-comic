<div class="card border border-base-300/70 bg-base-100 shadow-lg">
    <div class="card-body gap-3">
        <div class="flex items-center gap-3">
            <img src="{{ $item['comic']['cover'] }}" alt="{{ $item['comic']['title'] }} cover" class="w-14 rounded-lg border border-base-300/70" loading="lazy" decoding="async">
            <div class="min-w-0">
                <h3 class="truncate text-base font-semibold">{{ $item['comic']['title'] }}</h3>
                <p class="truncate text-xs text-base-content/60">{{ $item['chapter']['label'] }} · {{ $item['chapter']['title'] }}</p>
            </div>
        </div>

        <progress class="progress progress-primary w-full" value="{{ $item['progress'] }}" max="100"></progress>

        <div class="card-actions justify-between">
            <span class="text-xs text-base-content/60">{{ $item['label'] }}</span>
            <a href="{{ route('chapters.show', ['slug' => $item['comic']['slug'], 'chapter' => $item['chapter']['number']]) }}" class="btn btn-primary btn-xs">Lanjut</a>
        </div>
    </div>
</div>
