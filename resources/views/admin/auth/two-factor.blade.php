@extends('layouts.app', [
    'title' => 'Verifikasi Admin | Velmics',
    'description' => 'Verifikasi 2 langkah untuk panel admin Velmics.',
    'hideNavbar' => true,
    'hideFooter' => true,
    'hideAlerts' => true,
])

@section('content')
    <section class="mx-auto flex min-h-[75vh] w-full max-w-md items-center">
        <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Admin 2FA</div>
            <h1 class="mt-2 text-3xl font-semibold">Masukkan kode verifikasi</h1>
            <p class="mt-2 text-sm leading-7 text-base-content/65">
                Kode 6 digit sudah dikirim ke <span class="font-semibold">{{ $email }}</span>. Langkah ini wajib supaya panel admin tidak cukup dilindungi password saja.
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

            <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="mt-6 space-y-4">
                @csrf

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Kode 2FA</span></div>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="input input-bordered field-shell w-full text-center tracking-[0.45em]"
                        placeholder="123456" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus>
                </label>

                <div class="flex flex-wrap justify-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary rounded-2xl px-6">Verifikasi</button>
                    <button type="submit" form="admin-two-factor-resend" class="btn btn-ghost rounded-2xl border border-base-300/70">
                        Kirim ulang kode
                    </button>
                    <a href="{{ route('admin.login') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali</a>
                </div>
            </form>

            <form id="admin-two-factor-resend" method="POST" action="{{ route('admin.two-factor.resend') }}" class="hidden">
                @csrf
            </form>
        </div>
    </section>
@endsection
