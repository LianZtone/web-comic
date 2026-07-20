<div class="flex items-start gap-3">
    <div class="avatar shrink-0">
        <div class="h-10 w-10 overflow-hidden rounded-full border border-base-300/70 bg-base-200/40 shadow-sm"
            data-reader-comment-avatar>
            <img src="{{ $comment['avatar'] ?? asset('assets/images/default-avatar.jpeg') }}"
                class="h-full w-full object-cover" alt="Avatar {{ $comment['name'] }}" loading="lazy"
                decoding="async">
        </div>
    </div>

    <div class="min-w-0">
        <div class="font-semibold leading-5 text-base-content">{{ $comment['name'] }}</div>
        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-base-content/50">
            <span>{{ $comment['time'] }}</span>
            @if (($comment['is_spoiler'] ?? false) === true)
                <span class="badge badge-warning badge-xs rounded-full">spoiler</span>
            @endif
            @if (($comment['is_edited'] ?? false) === true)
                <span class="badge badge-ghost badge-xs rounded-full">diedit</span>
            @endif
        </div>
    </div>
</div>
