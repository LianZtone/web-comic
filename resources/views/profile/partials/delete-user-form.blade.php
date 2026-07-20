<section class="space-y-6 mb-5">
    <header>
        <h2 class="text-2xl font-semibold text-error">Hapus akun</h2>
        <p class="mt-2 text-sm leading-7 text-base-content/65">
            Tindakan ini permanen. Semua akses akun akan dihapus dan tidak bisa dikembalikan.
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
        @csrf
        @method('delete')

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Konfirmasi dengan Password</span></div>
            <div class="input input-bordered field-shell w-full field-shell-danger flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                </svg>
                <input id="password" name="password" type="password" class="field-input"
                    placeholder="Masukkan password untuk konfirmasi">
            </div>
            @error('password', 'userDeletion')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="btn btn-error rounded-2xl px-6 text-white">Hapus Akun</button>
            <span class="text-sm text-base-content/55">Pastikan kamu sudah yakin sebelum melanjutkan.</span>
        </div>
    </form>
</section>
