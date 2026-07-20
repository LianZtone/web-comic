@extends('layouts.app', [
    'title' => 'Reader Login | Velmics',
    'description' => 'Masuk ke akun pembaca untuk membuka bookmark, readlist, dan history pribadi.',
    'hideFooter' => true,
])

@section('content')
    <section class="mx-auto grid min-h-[75vh] w-full max-w-5xl gap-5 items-center
                grid-cols-1 md:grid-cols-2 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-[2rem] p-5">
            <div class=" w-full rounded-[2rem]">
                <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Reader</div>
                <h1 class="mt-2 text-3xl font-semibold">Masuk ke akun Velmics</h1>
                <p class="mt-2 text-sm leading-7 text-base-content/65">
                    Login untuk membuka bookmark, readlist, history, dan rak baca personalmu.
                </p>
            </div>
            <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
                @include('auth.partials.switcher')

                @if ($errors->any())
                    <div class="alert alert-error mt-5 rounded-[1.35rem]">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success mt-5 rounded-[1.35rem]">
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <label class="field-control">
                        <div class="field-label"><span class="field-label-text">Email</span></div>
                        <div class="input input-bordered field-shell w-full flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.312 0L2.25 6.75" />
                            </svg>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                                class="field-input" required autofocus autocomplete="username">
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
                            <input type="password" name="password" placeholder="Masukkan password" class="field-input"
                                required autocomplete="current-password">
                        </div>
                    </label>

                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary">
                        <span class="label-text">Tetap masuk di perangkat ini</span>
                    </label>

                    @include('partials.forms.captcha', [
                        'captchaField' => 'captcha_answer',
                        'captchaLabel' => 'Verifikasi manusia',
                        'captchaQuestion' => \App\Support\FormCaptcha::question(request(), 'reader-login'),
                    ])

                    <div class="flex flex-wrap gap-3 pt-2 justify-center">
                        <button type="submit" class="btn btn-primary rounded-2xl px-6">Login</button>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="btn btn-ghost rounded-2xl border border-base-300/70">Lupa Password</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
