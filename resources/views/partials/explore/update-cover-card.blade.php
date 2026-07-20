<article class="group">
    <a href="{{ route('chapters.show', ['slug' => $item['comic']['slug'], 'chapter' => $item['chapter']['number']]) }}"
        class="relative block overflow-hidden rounded-[1.6rem] border border-base-300/70 bg-base-100 shadow-lg transition duration-300 hover:-translate-y-1 hover:border-primary/25">
        <img src="{{ $item['comic']['cover'] }}" alt="{{ $item['comic']['title'] }} cover"
            class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy"
            decoding="async">

        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3 text-[11px] font-medium">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-black/65 px-2.5 py-1 text-white backdrop-blur-sm">
                    {{ $item['chapter']['release_label'] }}
                </span>
                <span class="rounded-full bg-primary/90 px-2.5 py-1 text-primary-content shadow-sm">
                    {{ $item['type'] }}
                </span>
            </div>
            <span class="rounded-full bg-base-100/90 px-2.5 py-1 text-base-content shadow-sm">
                {{ ucfirst($item['source']) }}
            </span>
        </div>

        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/70 to-transparent p-4 text-white">
            <div class="line-clamp-2 text-base font-semibold leading-tight">{{ $item['comic']['title'] }}</div>
            <div class="mt-1 text-xs text-white/75">{{ $item['chapter']['label'] }}</div>
        </div>
    </a>
</article>
