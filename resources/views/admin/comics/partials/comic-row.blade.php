<tr>
    <td class="min-w-[20rem]">
        <div class="flex items-start gap-3">
            <div class="h-20 w-14 shrink-0 overflow-hidden rounded-xl border border-base-300/70 bg-base-200">
                @if ($comic->cover_url)
                    <img src="{{ \App\Support\ComicMedia::resolveMediaPath($comic->cover_url) }}" alt="{{ $comic->title }} cover" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-[10px] uppercase tracking-[0.2em] text-base-content/35">No Cover</div>
                @endif
            </div>
            <div class="min-w-0">
                <div class="font-semibold">{{ $comic->title }}</div>
                <div class="mt-1 line-clamp-2 text-sm text-base-content/65">{{ $comic->tagline ?: $comic->summary }}</div>
                <div class="mt-2 text-xs text-base-content/50">Slug: {{ $comic->slug }}</div>
            </div>
        </div>
    </td>
    <td>
        <span class="badge badge-primary">{{ $comic->status }}</span>
    </td>
    <td>
        <div class="flex flex-wrap gap-1">
            <span class="badge badge-outline badge-sm">{{ $comic->comic_type ?? 'Manhwa' }}</span>
            <span class="badge badge-outline badge-sm">{{ $comic->source_type ?? 'Project' }}</span>
        </div>
    </td>
    <td>{{ $comic->chapters_count }}</td>
    <td>
        <div class="text-sm">{{ $comic->author }}</div>
        @if ($comic->artist)
            <div class="text-xs text-base-content/50">Artist: {{ $comic->artist }}</div>
        @endif
    </td>
    <td>{{ $comic->sort_order }}</td>
    <td>
        <div class="flex flex-wrap gap-1">
            @if ($comic->is_featured)
                <span class="badge badge-secondary badge-sm">Featured</span>
            @endif
            @if (isset($comic->is_recommended) && $comic->is_recommended)
                <span class="badge badge-accent badge-sm">Recommend</span>
            @endif
            @if (isset($comic->is_admin_pick) && $comic->is_admin_pick)
                <span class="badge badge-info badge-sm">Admin Pick</span>
            @endif
        </div>
    </td>
    <td class="min-w-[16rem]">
        <div class="flex justify-end gap-2 flex-wrap">
            <a href="{{ route('comics.show', $comic->slug) }}" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">Preview</a>
            <a href="{{ route('admin.chapters.create', $comic) }}" class="btn btn-outline btn-sm rounded-2xl">Chapter</a>
            <a href="{{ route('admin.comics.edit', $comic) }}" class="btn btn-primary btn-sm rounded-2xl">Edit</a>
            <form action="{{ route('admin.comics.destroy', $comic) }}" method="POST" onsubmit="return confirm('Hapus komik ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-sm rounded-2xl">Hapus</button>
            </form>
        </div>
    </td>
</tr>
