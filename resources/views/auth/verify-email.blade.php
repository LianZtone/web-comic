@extends('layouts.app', [
    'title' => 'Verifikasi Email | Velmics',
    'description' => 'Verifikasi email akun pembaca sebelum mulai memakai seluruh fitur personal Velmics.',
    'hideFooter' => true,
])

@section('content')
    <section class="mx-auto flex min-h-[75vh] w-full max-w-xl items-center">
        <div class="w-full rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-2xl backdrop-blur-sm sm:p-8">
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Verifikasi</div>
            <h1 class="mt-2 text-3xl font-semibold">Cek email kamu dulu</h1>
            <p class="mt-2 text-sm leading-7 text-base-content/65">
                Sebelum mulai pakai fitur personal, verifikasi alamat email dengan tautan yang sudah kami kirim. Kalau belum masuk, kamu bisa minta kirim ulang dari sini.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-5 rounded-[1.35rem]">
                    <span>Tautan verifikasi baru sudah dikirim ke email kamu.</span>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-2xl px-6">Kirim Ulang Verifikasi</button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost rounded-2xl border border-base-300/70">Logout</button>
                </form>
            </div>
        </div>
    </section>
@endsection
