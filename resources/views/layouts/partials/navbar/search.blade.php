{{-- ketika ctrl-k ditekan akan fokus pada input pencarian dan ketika sebuah nama komik di cari akan ditampilkan dalam bentuk daftar yang muncul dibawah input search atau menampilkan modal daftar komik --}}
<div
    x-data="{
        ...searchComponent(),
        mobileOpen: false,
        openSearch() {
            if (window.innerWidth < 1024) {
                this.mobileOpen = true;
                this.open = false;
                this.$nextTick(() => this.$refs.mobileSearch?.focus());
                return;
            }

            this.open = true;
            this.mobileOpen = false;
            this.$nextTick(() => this.$refs.search?.focus());
        },
        closeSearch() {
            this.open = false;
            this.mobileOpen = false;
        }
    }"
    @keydown.window.prevent.ctrl.k="openSearch()"
    @keydown.window.prevent.meta.k="openSearch()"
    @keydown.window.escape="closeSearch()"
    @click.away="if (window.innerWidth >= 1024) open = false"
    class="relative">
    <button type="button" class="btn btn-circle btn-sm border border-base-300/70 bg-base-100/80 lg:hidden"
        aria-label="Buka pencarian komik" @click="openSearch()">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
        </svg>
    </button>

    <form action="{{ route('comics.index') }}" method="GET" class="hidden lg:block">
        <label
            class="input rounded-2xl border border-base-300/70 bg-base-100/80 px-3 py-1 flex items-center gap-2">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none"
                    stroke="currentColor">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </g>
            </svg>
            <input x-ref="search" x-model="query" @input.debounce.300ms="search()"
                @focus="open = true" type="search" name="q" value="{{ request('q') }}"
                placeholder="Cari Komik" class="field-search-input grow" autocomplete="off" />
            <kbd class="kbd kbd-sm">⌘</kbd>
            <kbd class="kbd kbd-sm">K</kbd>
        </label>
    </form>

    <div x-show="open && (results.length > 0 || loading)" x-transition
        class="absolute right-0 mt-3 w-full rounded-lg border border-base-300/70 bg-base-100 p-4 shadow-2xl">
        <div x-show="loading" class="flex items-center gap-2">
            <p class="text-sm text-base-content/70">Mencari komik...</p>
        </div>
        <template x-if="results.length > 0">
            <ul class="max-h-60 overflow-y-auto">
                <template x-for="comic in results" :key="comic.id">
                    <li>
                        <a :href="`/comics/${comic.slug}`"
                            class="flex items-center gap-3 rounded-lg p-2 hover:bg-primary/10">
                            <img :src="comic.cover_url" alt=""
                                class="h-10 w-10 rounded-md object-cover">
                            <div>
                                <p class="text-sm font-medium" x-text="comic.title"></p>
                                <p class="text-xs text-base-content/70" x-text="comic.author"></p>
                            </div>
                        </a>
                    </li>
                </template>
            </ul>
        </template>
        <template x-show="!loading && results.length === 0 && query.length > 0">
            <p class="text-sm text-base-content/70">Tidak ada komik ditemukan.</p>
        </template>
    </div>

    <div x-show="mobileOpen" x-transition.opacity class="fixed inset-0 z-[80] lg:hidden" style="display: none;">
        <div class="absolute inset-0 bg-base-content/35 backdrop-blur-sm" @click="closeSearch()"></div>

        <div class="absolute inset-0 flex flex-col bg-base-100">
            <div class="border-b border-base-300/70 px-4 py-4">
                <div class="flex items-center gap-3">
                    <form action="{{ route('comics.index') }}" method="GET" class="flex-1">
                        <label class="input flex w-full items-center gap-2 rounded-2xl border border-base-300/70 bg-base-100 px-3">
                            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none"
                                    stroke="currentColor">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </g>
                            </svg>
                            <input x-ref="mobileSearch" x-model="query" @input.debounce.300ms="search()"
                                type="search" name="q" value="{{ request('q') }}" placeholder="Cari Komik"
                                class="field-search-input grow" autocomplete="off" />
                        </label>
                    </form>

                    <button type="button" class="btn btn-ghost btn-sm rounded-2xl"
                        @click="closeSearch()">Tutup</button>
                </div>
                <p class="mt-3 text-xs text-base-content/55">Cari judul komik, kreator, atau langsung buka halaman katalog lengkap.</p>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4">
                <div x-show="loading" class="rounded-[1.5rem] border border-base-300/70 bg-base-100 p-4">
                    <p class="text-sm text-base-content/70">Mencari komik...</p>
                </div>

                <template x-if="results.length > 0">
                    <div class="space-y-2">
                        <template x-for="comic in results" :key="comic.id">
                            <a :href="`/comics/${comic.slug}`" class="flex items-center gap-3 rounded-[1.5rem] border border-base-300/70 bg-base-100 p-3 shadow-sm transition hover:border-primary/20 hover:bg-primary/5">
                                <img :src="comic.cover_url" alt="" class="h-16 w-12 rounded-xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold" x-text="comic.title"></p>
                                    <p class="mt-1 text-xs text-base-content/65" x-text="comic.author"></p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/35" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 6 6 6-6 6" />
                                </svg>
                            </a>
                        </template>
                    </div>
                </template>

                <div x-show="!loading && results.length === 0 && query.length > 0"
                    class="rounded-[1.5rem] border border-dashed border-base-300/70 bg-base-100/70 p-5 text-sm text-base-content/65">
                    Tidak ada komik ditemukan.
                </div>

                <div x-show="query.length < 2" class="rounded-[1.5rem] border border-base-300/70 bg-base-100/70 p-5">
                    <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Pencarian</div>
                    <p class="mt-2 text-sm text-base-content/70">Ketik minimal dua huruf untuk mulai mencari, atau buka katalog lengkap jika ingin pakai filter genre dan status.</p>
                    <a href="{{ route('comics.index') }}" class="btn btn-primary btn-sm mt-4 rounded-2xl">Buka Katalog</a>
                </div>
            </div>
        </div>
    </div>
</div>
