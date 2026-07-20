<li>
    <a href="{{ route('chapters.show', ['slug' => $comicSlug, 'chapter' => $item['number']]) }}" @class([
        'active rounded-xl bg-primary text-primary-content' => $item['number'] === $currentChapterNumber,
        'rounded-xl' => $item['number'] !== $currentChapterNumber,
    ])>
        <div>
            <div class="font-semibold">{{ $item['label'] }}</div>
            <div class="text-xs opacity-70">{{ $item['title'] }}</div>
        </div>
    </a>
</li>
