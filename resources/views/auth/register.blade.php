@extends('layouts.app', [
    'title' => 'Register | Velmics',
    'description' => 'Buat akun pembaca untuk menyimpan bookmark, readlist, dan history di Velmics.',
    'hideFooter' => true,
])

@section('content')
    <section class="mx-auto grid min-h-[75vh] w-full max-w-5xl gap-5 items-center
                grid-cols-1 md:grid-cols-2 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-[2rem] p-5">

            <div class="rounded-[2rem] ">
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Reader</div>
                <h1 class="mt-2 text-3xl font-semibold">Buat akun Velmics</h1>
                <p class="mt-2 text-sm leading-7 text-base-content/65">
                    Daftar untuk menyimpan bookmark, readlist, history, dan rak baca personalmu.
                </p>
            </div>

            <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
                @include('auth.partials.switcher')

                @if ($errors->any())
                    <div class="alert alert-error mt-5 rounded-[1.35rem]">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                    @csrf

                    <label class="field-control">
                        <div class="field-label"><span class="field-label-text">Nama</span></div>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input input-bordered field-shell w-full" required autofocus autocomplete="name">
                    </label>

                    <label class="field-control">
                        <div class="field-label"><span class="field-label-text">Email</span></div>
                        <div class="input input-bordered field-shell w-full flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.312 0L2.25 6.75" />
                            </svg>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                                class="field-input " required autocomplete="username">
                        </div>
                    </label>

                    <label class="field-control">
                        <div class="field-label"><span class="field-label-text">Password</span></div>
                        <div class="input input-bordered field-shell w-full flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                            </svg>
                            <input type="password" name="password" placeholder="Minimal 8 karakter" class="field-input"
                                required autocomplete="new-password">
                        </div>
                    </label>

                    <label class="field-control">
                        <div class="field-label"><span class="field-label-text">Konfirmasi Password</span></div>
                        <div class="input input-bordered field-shell w-full flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                            </svg>
                            <input type="password" name="password_confirmation" placeholder="Ulangi password"
                                class="field-input" required autocomplete="new-password">
                        </div>
                    </label>

                    <div class="flex flex-wrap gap-3 pt-2 justify-center">
                        <button type="submit" class="btn btn-primary rounded-2xl px-6">Register</button>
                        <a href="{{ route('login') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Sudah
                            punya akun?</a>
                    </div>
                </form>
            </div>
        </div>

    </section>
@endsection
