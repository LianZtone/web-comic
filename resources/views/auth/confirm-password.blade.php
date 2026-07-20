@extends('layouts.app', [
    'title' => 'Konfirmasi Password | Velmics',
    'description' => 'Konfirmasi password untuk melanjutkan ke area akun yang sensitif.',
    'hideFooter' => true,
])

@section('content')
    <section class="mx-auto flex min-h-[75vh] w-full max-w-md items-center">
        <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Konfirmasi</div>
            <h1 class="mt-2 text-3xl font-semibold">Konfirmasi password</h1>
            <p class="mt-2 text-sm leading-7 text-base-content/65">
                Area ini sensitif. Masukkan password akunmu sekali lagi sebelum melanjutkan.
            </p>

            @if ($errors->any())
                <div class="alert alert-error mt-5 rounded-[1.35rem]">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
                @csrf

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Password</span></div>
                    <div class="input input-bordered field-shell flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                        </svg>
                        <input id="password" type="password" name="password" class="field-input"
                            placeholder="Masukkan password akunmu" required autocomplete="current-password">
                    </div>
                </label>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary rounded-2xl px-6">Konfirmasi</button>
                </div>
            </form>
        </div>
    </section>
@endsection
