@extends('layouts.app', [
    'title' => 'Admin Login | Velmics',
    'description' => 'Masuk ke panel admin Velmics.',
    'hideNavbar' => true,
    'hideFooter' => true,
    'hideAlerts' => true,
])

@section('content')
    <section class="mx-auto flex min-h-[75vh] w-full max-w-md items-center">
        <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Admin</div>
            <h1 class="mt-2 text-3xl font-semibold">Masuk ke panel Velmics</h1>
            <p class="mt-2 text-sm leading-7 text-base-content/65">
                Gunakan akun admin untuk mengelola katalog, chapter, dan data backend.
            </p>

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

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
                @csrf

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Email</span></div>
                    <div class="input input-bordered field-shell w-full flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.312 0L2.25 6.75" />
                        </svg>
                        <input type="email" name="email" value="{{ old('email') }}" class="field-input"
                            placeholder="admin@velmics.test" required autofocus>
                    </div>
                </label>

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Password</span></div>
                    <div class="input input-bordered field-shell w-full flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="field-icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                        </svg>
                        <input type="password" name="password" class="field-input" placeholder="Masukkan password admin"
                            required>
                    </div>
                </label>

                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="remember" value="1" class="checkbox checkbox-sm checkbox-primary">
                    <span class="label-text">Tetap masuk di perangkat ini</span>
                </label>

                @include('partials.forms.captcha', [
                    'captchaField' => 'captcha_answer',
                    'captchaLabel' => 'Verifikasi manusia',
                    'captchaQuestion' => \App\Support\FormCaptcha::question(request(), 'admin-login'),
                ])

                <div class="flex flex-wrap gap-3 pt-2 justify-center">
                    <button type="submit" class="btn btn-primary rounded-2xl px-6">Login Admin</button>
                    <a href="{{ route('home') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali</a>
                </div>
            </form>
        </div>
    </section>
@endsection
