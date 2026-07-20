@extends('layouts.app', [
    'title' => 'Atur Password Baru | Velmics',
    'description' => 'Buat password baru untuk kembali masuk ke akun Velmics.',
    'hideFooter' => true,
])

@section('content')
    <section class="mx-auto flex min-h-[75vh] w-full max-w-md items-center">
        <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Password Baru</div>
            <h1 class="mt-2 text-3xl font-semibold">Atur ulang password</h1>
            <p class="mt-2 text-sm leading-7 text-base-content/65">
                Masukkan email dan password baru untuk mengaktifkan kembali akunmu.
            </p>

            @if ($errors->any())
                <div class="alert alert-error mt-5 rounded-[1.35rem]">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Email</span></div>
                    <div class="input input-bordered field-shell flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.312 0L2.25 6.75" />
                        </svg>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="nama@email.com"
                            class="field-input"
                            required autofocus autocomplete="username">
                    </div>
                </label>

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Password Baru</span></div>
                    <div class="input input-bordered field-shell flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                        </svg>
                        <input id="password" type="password" name="password" placeholder="Minimal 8 karakter"
                            class="field-input"
                            required autocomplete="new-password">
                    </div>
                </label>

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Konfirmasi Password</span></div>
                    <div class="input input-bordered field-shell flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                        </svg>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password baru"
                            class="field-input"
                            required autocomplete="new-password">
                    </div>
                </label>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn btn-primary rounded-2xl px-6">Simpan Password Baru</button>
                    <a href="{{ route('login') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </section>
@endsection
