<section class="space-y-6">
    <header>
        <h2 class="text-2xl font-semibold">Password & keamanan</h2>
        <p class="mt-2 text-sm leading-7 text-base-content/65">
            Gunakan password yang panjang dan unik untuk menjaga akun tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Password Saat Ini</span></div>
            <div class="input input-bordered field-shell w-full flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                </svg>
                <input id="update_password_current_password" name="current_password" type="password" placeholder="Password yang sekarang"
                    class="field-input"
                    autocomplete="current-password">
            </div>
            @error('current_password', 'updatePassword')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Password Baru</span></div>
            <div class="input input-bordered field-shell w-full flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                </svg>
                <input id="update_password_password" name="password" type="password" placeholder="Minimal 8 karakter"
                    class="field-input"
                    autocomplete="new-password">
            </div>
            @error('password', 'updatePassword')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Konfirmasi Password Baru</span></div>
            <div class="input input-bordered field-shell w-full flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                </svg>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password baru"
                    class="field-input"
                    autocomplete="new-password">
            </div>
            @error('password_confirmation', 'updatePassword')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="btn btn-primary rounded-2xl px-6">Perbarui Password</button>

            @if (session('status') === 'password-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success">
                    Password berhasil diperbarui.
                </span>
            @endif
        </div>
    </form>
</section>
