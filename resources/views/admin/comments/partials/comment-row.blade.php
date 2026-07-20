<tr>
    <td>
        <label class="flex justify-center">
            <input type="checkbox" name="comment_ids[]" value="{{ $comment->id }}" class="checkbox checkbox-sm" data-bulk-item>
        </label>
    </td>
    <td class="min-w-[12rem]">
        <div class="font-semibold">{{ $comment->display_name }}</div>
        @include('admin.users.partials.moderation-badges', ['user' => $comment->user])
    </td>
    <td class="min-w-[24rem]">
        @if ($comment->is_spoiler)
            <div class="mb-2">
                <span class="badge badge-warning badge-sm">Spoiler</span>
            </div>
        @endif
        <div class="line-clamp-3 text-sm leading-6 text-base-content/80">{{ $comment->body }}</div>
    </td>
    <td class="min-w-[18rem]">
        <div class="text-sm font-medium">{{ $comment->chapter?->comic?->title ?? 'Komik tidak ditemukan' }}</div>
        <div class="text-xs text-base-content/55">{{ $comment->chapter?->title ?? 'Chapter tidak ditemukan' }}</div>
    </td>
    <td>
        <span class="badge {{ $comment->is_visible ? 'badge-success' : 'badge-warning' }}">
            {{ $comment->is_visible ? 'Visible' : 'Hidden' }}
        </span>
    </td>
    <td>{{ $comment->likes_count }}</td>
    <td class="text-sm text-base-content/60">{{ $comment->created_at?->diffForHumans() ?? 'Baru saja' }}</td>
    <td class="min-w-[16rem]">
        <div class="space-y-2">
            <div class="flex flex-wrap justify-end gap-2">
                @if ($comment->chapter?->comic)
                    <a href="{{ route('chapters.show', ['slug' => $comment->chapter->comic->slug, 'chapter' => $comment->chapter->number]) }}"
                        class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">
                        Lihat
                    </a>
                @endif

                <form method="POST" action="{{ route('admin.comments.visibility', $comment) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_visible" value="{{ $comment->is_visible ? 0 : 1 }}">
                    <button type="submit" class="btn btn-outline btn-sm rounded-2xl">
                        {{ $comment->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-sm rounded-2xl">Hapus</button>
                </form>
            </div>

            @include('admin.users.partials.moderation-actions', ['user' => $comment->user])
        </div>
    </td>
</tr>
