<article class="card border border-base-300/70 bg-base-100 shadow-lg">
    <figure class="border-b border-base-300/70">
        <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
    </figure>
    <div class="card-body gap-3 p-4">
        <h3 class="line-clamp-2 text-sm font-semibold sm:text-base">{{ $comic['title'] }}</h3>
        <div class="flex flex-wrap gap-2 text-[11px] text-base-content/60">
            <span>{{ $comic['chapter_total'] }} ch</span>
            <span>{{ $comic['status'] }}</span>
        </div>
        <div class="card-actions justify-end">
            <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['first_chapter']['number']]) }}" class="btn btn-primary btn-xs">Baca</a>
        </div>
    </div>
</article>
