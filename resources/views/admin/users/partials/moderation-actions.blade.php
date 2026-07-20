@php
    $defaultReason = 'Spoiler berlebihan atau komentar yang mengganggu pengalaman baca.';
    $canModerateUser = $user && ! $user->is_admin;
@endphp

@if ($canModerateUser)
    <div class="flex flex-wrap justify-end gap-2">
        <form method="POST" action="{{ route('admin.users.warn', $user) }}">
            @csrf
            <input type="hidden" name="reason" value="{{ $defaultReason }}">
            <button type="submit" class="btn btn-ghost btn-sm rounded-2xl border border-base-300/70">
                Warn
            </button>
        </form>

        @if (! $user->hasCommentRestriction())
            <form method="POST" action="{{ route('admin.users.hide-comments', $user) }}"
                onsubmit="return confirm('Sembunyikan semua komentar user ini dan batasi komentar baru?')">
                @csrf
                <input type="hidden" name="reason" value="{{ $defaultReason }}">
                <button type="submit" class="btn btn-outline btn-sm rounded-2xl">
                    Hide semua
                </button>
            </form>
        @endif

        @if (! $user->hasActiveSuspension() && ! $user->isBanned())
            <form method="POST" action="{{ route('admin.users.suspend', $user) }}"
                onsubmit="return confirm('Suspend user ini selama 7 hari?')">
                @csrf
                <input type="hidden" name="duration_days" value="7">
                <input type="hidden" name="reason" value="{{ $defaultReason }}">
                <button type="submit" class="btn btn-warning btn-sm rounded-2xl">
                    Suspend 7 hari
                </button>
            </form>
        @endif

        @if (! $user->isBanned())
            <form method="POST" action="{{ route('admin.users.ban', $user) }}"
                onsubmit="return confirm('Ban user ini dan sembunyikan semua komentarnya?')">
                @csrf
                <input type="hidden" name="reason" value="{{ $defaultReason }}">
                <button type="submit" class="btn btn-error btn-sm rounded-2xl">
                    Ban
                </button>
            </form>
        @endif

        @if ($user->hasCommentRestriction() || $user->hasActiveSuspension() || $user->isBanned())
            <form method="POST" action="{{ route('admin.users.clear-restrictions', $user) }}"
                onsubmit="return confirm('Buka kembali pembatasan user ini?')">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm rounded-2xl border border-success/30 text-success">
                    Cabut blokir
                </button>
            </form>
        @endif
    </div>
@endif
