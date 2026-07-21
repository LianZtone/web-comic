<article data-chapter-item data-chapter-number="{{ $chapter['number'] }}">
    <a href="{{ route('chapters.show', ['slug' => $comicSlug, 'chapter' => $chapter['number']]) }}"
        class="group flex items-center justify-between gap-4 rounded-[1.2rem] border border-base-300/70 bg-base-100/70 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-base-100"
        data-chapter-link data-comic-slug="{{ $comicSlug }}" data-chapter-number="{{ $chapter['number'] }}">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="hidden text-success" data-chapter-checkmark>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                <h3 class="text-base font-semibold text-base-content group-hover:text-primary">
                    {{ $chapter['label'] }}
                </h3>
                @if ($chapter['is_latest'])
                    <span class="badge badge-secondary badge-xs">Baru</span>
                @endif
                <span class="badge badge-success badge-xs hidden" data-chapter-read-badge>Sudah dibaca</span>
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-base-content/50">
                @if (!empty($chapter['title']))
                    <span class="truncate">{{ $chapter['title'] }}</span>
                    <span class="text-base-content/25">•</span>
                @endif
                <span>{{ $chapter['release_label'] }}</span>
            </div>
        </div>

        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-base-300/70 bg-base-100/80 text-base-content/45 transition group-hover:border-primary/30 group-hover:text-primary"
            data-chapter-arrow>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
            </svg>
        </span>
    </a>
</article>
