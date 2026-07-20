<figure class="mx-auto max-w-4xl">
    <div class="relative min-h-[18rem] overflow-hidden  bg-base-200/40 sm:min-h-[28rem]"
        data-reader-page-card>
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center p-4"
            data-reader-page-skeleton>
            <div class="skeleton h-full min-h-[18rem] w-full rounded-[1.4rem] sm:min-h-[28rem]"></div>
        </div>

        <img
            src="{{ $page['image'] }}"
            alt="{{ $chapterTitle }} page {{ $page['number'] }}"
            class="relative z-10 block w-full opacity-0 transition-opacity duration-300"
            data-reader-page-image
            decoding="async"
            @if ($isFirst)
                fetchpriority="high"
            @else
                loading="lazy"
            @endif
        >
    </div>

    @if (trim((string) ($page['caption'] ?? '')) !== '')
        <figcaption class="sr-only">{{ $page['caption'] }}</figcaption>
    @endif
</figure>
