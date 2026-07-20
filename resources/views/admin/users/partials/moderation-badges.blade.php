@if ($user)
    <div class="mt-2 flex flex-wrap gap-1">
        @if (($user->warning_count ?? 0) > 0)
            <span class="badge badge-outline badge-sm">{{ $user->warning_count }} warning</span>
        @endif

        @if ($user->hasCommentRestriction())
            <span class="badge badge-warning badge-sm">Komentar dibatasi</span>
        @endif

        @if ($user->hasActiveSuspension())
            <span class="badge badge-warning badge-sm">Suspend sampai {{ $user->suspended_until?->format('d M H:i') }}</span>
        @endif

        @if ($user->isBanned())
            <span class="badge badge-error badge-sm">Banned</span>
        @endif
    </div>
@endif
