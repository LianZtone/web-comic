@extends('layouts.app', [
    'title' => 'Pesan | Velmics',
    'description' => 'Lihat notifikasi balasan komentar dan aktivitas terbaru untuk akunmu.',
])

@section('content')
    <section class="space-y-5">
        <div>
            <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Inbox</div>
            <h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Pesan</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-base-content/65">
                Semua notifikasi balasan komentar akan muncul di sini.
            </p>
        </div>

        <div class="space-y-3">
            @forelse ($notifications as $notification)
                @php($data = $notification->data)
                <a href="{{ $data['url'] ?? route('home') }}"
                    class="block rounded-[1.4rem] border border-base-300/70 bg-base-100/80 p-5 shadow-sm transition hover:border-primary/20 hover:bg-primary/5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-base-content">
                                {{ $data['title'] ?? 'Notifikasi baru' }}
                            </div>
                            <div class="mt-1 text-sm leading-7 text-base-content/70">
                                {{ $data['message'] ?? 'Ada aktivitas baru di akunmu.' }}
                            </div>

                            @if (! empty($data['excerpt']))
                                <div class="mt-3 rounded-2xl border border-base-300/70 bg-base-200/40 px-4 py-3 text-sm text-base-content/60">
                                    "{{ $data['excerpt'] }}"
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-xs text-base-content/45">
                            {{ optional($notification->created_at)?->diffForHumans() }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-[1.4rem] border border-dashed border-base-300/70 bg-base-100/70 p-8 text-center text-sm text-base-content/60">
                    Belum ada notifikasi baru.
                </div>
            @endforelse
        </div>
    </section>
@endsection
