{{-- button next chapter and previous chapter --}}
@props(['comic', 'nextChapter', 'previousChapter'])

<div class="flex items-center justify-center  gap-5">
    @if ($previousChapter)
        <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $previousChapter['number']]) }}"
            class="btn btn-ghost rounded-full border border-base-300/70 ">
            {{-- icon arrow back --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
            </svg>
        </a>
    @else
        {{-- jika chapter sebelumnya tidak ada maka tidak akan kembali ke chapter sebelumnya --}}
        <div></div>
    @endif

    @if ($nextChapter)
        <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $nextChapter['number']]) }}"
            class="btn btn-ghost rounded-full border border-base-300/70">
            {{-- icon arrow forward --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
            </svg>
        </a>
    @else
        <div></div>
    @endif
</div>
