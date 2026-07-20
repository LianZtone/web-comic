@if (session('success'))
    <div class="pointer-events-none fixed right-4 top-4 z-[70] w-full max-w-sm sm:right-6 sm:top-6" data-flash-toast>
        <div class="pointer-events-auto overflow-hidden rounded-[1.5rem] border border-success/20 bg-base-100 shadow-2xl">
            <div class="h-1.5 w-full bg-success/80" data-flash-toast-bar></div>

            <div class="p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="badge badge-success badge-outline">Berhasil</div>
                        <h2 id="admin-flash-title" class="text-base font-semibold">Perubahan tersimpan</h2>
                        <p class="text-sm leading-6 text-base-content/70">{{ session('success') }}</p>
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
@endif
