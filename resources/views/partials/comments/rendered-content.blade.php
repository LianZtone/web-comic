@php
    $text = trim((string) ($comment['text'] ?? ''));
    $imageUrl = trim((string) ($comment['image_url'] ?? ''));
    $isSpoiler = ($comment['is_spoiler'] ?? false) === true;
@endphp

@if ($isSpoiler)
    <details class="group mt-3 overflow-hidden rounded-[1.2rem] border border-warning/30 bg-warning/10 shadow-sm [&_summary::-webkit-details-marker]:hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-medium text-base-content/75">
            <span class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-warning/30 bg-warning/15 text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.615 3.892 1.674 18.25A1.5 1.5 0 0 0 2.958 20.5h18.084a1.5 1.5 0 0 0 1.284-2.25L13.385 3.892a1.5 1.5 0 0 0-2.77 0Z" />
                    </svg>
                </span>
                <span>
                    <span class="group-open:hidden">Tampilkan spoiler</span>
                    <span class="hidden group-open:inline">Sembunyikan spoiler</span>
                </span>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 shrink-0 transition-transform duration-200 group-open:rotate-180"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
            </svg>
        </summary>

        <div class="space-y-3 border-t border-warning/20 px-4 py-4">
            @if ($text !== '')
                <div class="text-sm leading-7 text-base-content/80">
                    {!! \App\Support\CommentMarkup::toHtml($text) !!}
                </div>
            @endif

            @if ($imageUrl !== '')
                <div class="flex">
                    <a href="{{ $imageUrl }}" target="_blank" rel="noreferrer"
                        class="group inline-flex overflow-hidden rounded-[1rem] border border-base-content/10 bg-base-100/80 shadow-sm transition hover:border-primary/20 hover:bg-base-100">
                        <img src="{{ $imageUrl }}" alt="Gambar komentar"
                            class="h-[100px] w-[100px] bg-base-200/40 object-cover transition duration-200 group-hover:scale-[1.03]"
                            loading="lazy" decoding="async">
                    </a>
                </div>
            @endif
        </div>
    </details>
@elseif ($text !== '' || $imageUrl !== '')
    <div class="mt-3 space-y-3">
        @if ($text !== '')
            <div class="text-sm leading-7 text-base-content/80">
                {!! \App\Support\CommentMarkup::toHtml($text) !!}
            </div>
        @endif

        @if ($imageUrl !== '')
            <div class="flex">
                <a href="{{ $imageUrl }}" target="_blank" rel="noreferrer"
                    class="group inline-flex overflow-hidden rounded-[1rem] border border-base-content/10 bg-base-100/80 shadow-sm transition hover:border-primary/20 hover:bg-base-100">
                    <img src="{{ $imageUrl }}" alt="Gambar komentar"
                        class="h-[100px] w-[100px] bg-base-200/40 object-cover transition duration-200 group-hover:scale-[1.03]"
                        loading="lazy" decoding="async">
                </a>
            </div>
        @endif
    </div>
@endif
