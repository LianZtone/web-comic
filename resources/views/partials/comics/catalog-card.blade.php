<article class="overflow-hidden rounded-[1.5rem] border border-base-300/70 bg-base-100/60 shadow-sm transition hover:-translate-y-1 hover:border-primary/20 hover:bg-base-100">
    <a href="{{ route('comics.show', $comic['slug']) }}" class="block">
        <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
    </a>

    <div class="space-y-2 p-3">
        <div class="flex flex-wrap gap-1.5 text-[11px]">
            <span class="badge badge-primary badge-sm">{{ $comic['status'] }}</span>
            <span class="badge badge-outline badge-sm border-base-300/70">{{ $comic['comic_type'] }}</span>
        </div>

        <div>
            <a href="{{ route('comics.show', $comic['slug']) }}" class="line-clamp-2 text-sm font-semibold leading-5 sm:text-base">
                {{ $comic['title'] }}
            </a>
            <p class="mt-1 line-clamp-2 text-[11px] text-base-content/60 sm:text-xs">{{ $comic['tagline'] }}</p>
        </div>

        <div class="space-y-1 text-[11px] text-base-content/55">
            <div class="truncate">{{ $comic['author'] }}</div>
            <div class="flex items-center gap-2">
                <span>{{ $comic['chapter_total'] }} ch</span>
                <span>•</span>
                <span>{{ $comic['views_label'] }} views</span>
            </div>
        </div>

        <div class="flex gap-2 pt-1">
            <a href="{{ route('comics.show', $comic['slug']) }}" class="btn btn-ghost btn-xs flex-1 rounded-xl border border-base-300/70">Detail</a>
            <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['first_chapter']['number']]) }}" class="btn btn-primary btn-xs flex-1 rounded-xl">Baca</a>
        </div>
    </div>
</article>
