<article class="space-y-3" data-update-item data-update-source="{{ $source }}">
    <div class="overflow-hidden rounded-[1.4rem] border border-base-300/70 bg-base-200 shadow-lg transition hover:-translate-y-1" data-update-card>
        <a href="{{ route('comics.show', $item['comic']['slug']) }}" class="block" data-update-link>
            <img src="{{ $item['comic']['cover'] }}" alt="{{ $item['comic']['title'] }} cover" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async" data-update-image>
        </a>
        <div class="space-y-3 p-3" data-update-content>
            <div class="line-clamp-2 text-base font-semibold leading-tight">{{ $item['comic']['title'] }}</div>

            <div class="flex items-center gap-2 text-xs">
                <span class="badge badge-error border-0 text-[10px] text-white">UP</span>
                <span class="text-base-content">
                    {{ count($item['chapters']) > 1 ? '2 chapter terbaru' : 'Chapter terbaru' }}
                </span>
            </div>

            <div class="space-y-2">
                @foreach ($item['chapters'] as $chapter)
                    <a href="{{ route('chapters.show', ['slug' => $item['comic']['slug'], 'chapter' => $chapter['number']]) }}"
                        class="flex items-center justify-between gap-2 rounded-xl bg-base-100 px-3 py-2.5 text-sm transition hover:bg-base-100/80"
                        data-update-chapter-link
                        data-comic-slug="{{ $item['comic']['slug'] }}"
                        data-chapter-number="{{ $chapter['number'] }}">
                        <span class="truncate font-medium">{{ $chapter['label'] }}</span>
                        <span class="badge badge-error badge-sm shrink-0 border-0 text-[10px] text-white" data-update-new-badge>New</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</article>
