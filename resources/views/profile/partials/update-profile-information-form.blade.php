<section class="space-y-6">
    <header>
        <h2 class="text-2xl font-semibold">Profil pembaca</h2>
        <p class="mt-2 text-sm leading-7 text-base-content/65">
            Perbarui nama dan email yang dipakai untuk masuk dan menerima notifikasi akun.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Nama</span></div>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                class="input input-bordered field-shell w-full"
                required autofocus autocomplete="name">
            @error('name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Email</span></div>
            <div class="input input-bordered field-shell w-full flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.312 0L2.25 6.75" />
                </svg>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com"
                    class="field-input"
                    required autocomplete="username">
            </div>
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-[1.35rem] border border-warning/20 bg-warning/10 p-4 text-sm text-base-content/70">
                <p>Email kamu belum terverifikasi.</p>
                <button form="send-verification" class="mt-3 btn btn-ghost btn-sm rounded-2xl border border-base-300/70">
                    Kirim ulang verifikasi
                </button>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-3 text-success">Tautan verifikasi baru sudah dikirim.</p>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="btn btn-primary rounded-2xl px-6">Simpan Perubahan</button>

            @if (session('status') === 'profile-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success">
                    Profil berhasil diperbarui.
                </span>
            @endif
        </div>
    </form>
</section>
