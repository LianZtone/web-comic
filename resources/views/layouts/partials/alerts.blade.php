@php
    $flashMessages = collect([
        [
            'key' => 'success',
            'message' => session('success'),
            'label' => 'Berhasil',
            'title' => 'Aksi selesai',
            'variant' => 'success',
        ],
        [
            'key' => 'status',
            'message' => session('status'),
            'label' => 'Info',
            'title' => 'Pembaruan status',
            'variant' => 'success',
        ],
        [
            'key' => 'error',
            'message' => session('error'),
            'label' => 'Error',
            'title' => 'Ada masalah',
            'variant' => 'error',
        ],
    ])->filter(fn (array $item) => filled($item['message']))->values();
@endphp

@if ($flashMessages->isNotEmpty())
    <div class="pointer-events-none fixed right-4 top-4 z-[70] flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
        @foreach ($flashMessages as $flash)
            <div class="pointer-events-auto" data-flash-toast data-flash-toast-duration="5000">
                <div @class([
                    'overflow-hidden rounded-[1.5rem] border bg-base-100 shadow-2xl backdrop-blur',
                    'border-success/20' => $flash['variant'] === 'success',
                    'border-warning/20' => $flash['variant'] === 'warning',
                    'border-error/20' => $flash['variant'] === 'error',
                ])>
                    <div @class([
                        'h-1.5 w-full',
                        'bg-success/80' => $flash['variant'] === 'success',
                        'bg-warning/80' => $flash['variant'] === 'warning',
                        'bg-error/80' => $flash['variant'] === 'error',
                    ]) data-flash-toast-bar></div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div @class([
                                    'badge badge-outline',
                                    'badge-success' => $flash['variant'] === 'success',
                                    'badge-warning' => $flash['variant'] === 'warning',
                                    'badge-error' => $flash['variant'] === 'error',
                                ])>{{ $flash['label'] }}</div>
                                <h2 class="text-base font-semibold">{{ $flash['title'] }}</h2>
                                <p class="text-sm leading-6 text-base-content/70">{{ $flash['message'] }}</p>
                            </div>

                            <button type="button" class="btn btn-ghost btn-circle btn-sm shrink-0"
                                aria-label="Tutup notifikasi" data-flash-toast-close>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="button" class="btn btn-primary btn-sm rounded-2xl px-4" data-flash-toast-close>Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
