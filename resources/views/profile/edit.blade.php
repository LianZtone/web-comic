@extends('layouts.app', [
    'title' => 'Pengaturan Akun | Velmics',
    'description' => 'Kelola profil, password, dan keamanan akun pembaca kamu di Velmics.',
])

@section('content')
    <section class="space-y-5 mb-5">
        <article class="rounded-[2rem] border border-base-300/70 bg-base-100/80 p-6 shadow-lg sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary">Akun</span>
                <span class="badge badge-outline border-base-300/70">Pengaturan</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-semibold leading-tight sm:text-4xl">
                Pengaturan akun
            </h1>
            <p class="mt-3 max-w-2xl text-base-content/70">
                Kelola identitas pembaca, email, keamanan password, dan akses akun dari satu tempat yang rapi.
            </p>
        </article>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-5">
                <div class="rounded-[1.75rem] border border-base-300/70 bg-base-100/70 p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="rounded-[1.75rem] border border-base-300/70 bg-base-100/70 p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="rounded-[1.75rem] border border-error/20 bg-base-100/70 p-5 shadow-sm sm:p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-[1.75rem] border border-base-300/70 bg-base-100/70 p-5 shadow-sm">
                    <div class="text-xs uppercase tracking-[0.24em] text-base-content/45">Ringkasan</div>
                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 text-sm font-semibold text-primary">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-lg font-semibold">{{ auth()->user()->name }}</div>
                            <div class="truncate text-sm text-base-content/60">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-base-300/70 bg-base-100/70 p-5 shadow-sm">
                    <div class="text-xs uppercase tracking-[0.24em] text-base-content/45">Navigasi akun</div>
                    <div class="mt-4 space-y-2">
                        <a href="{{ route('library') }}" class="btn btn-ghost w-full justify-start rounded-2xl border border-base-300/70">Library</a>
                        <a href="{{ route('library.bookmarks') }}" class="btn btn-ghost w-full justify-start rounded-2xl border border-base-300/70">Bookmark</a>
                        <a href="{{ route('library.readlist') }}" class="btn btn-ghost w-full justify-start rounded-2xl border border-base-300/70">Readlist</a>
                        <a href="{{ route('library.history') }}" class="btn btn-ghost w-full justify-start rounded-2xl border border-base-300/70">History</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
