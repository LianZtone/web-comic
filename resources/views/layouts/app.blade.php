<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate" data-default-theme="corporate" data-dark-theme="dark"
    data-reader-storage-scope="{{ auth()->check() ? 'user-'.auth()->id() : 'guest' }}">

<head>
    @include('layouts.partials.head', [
        'title' => $title ?? 'Velmics | Portal Komik Digital',
        'description' =>
            $description ??
            'Portal baca komik digital dengan katalog ringkas, update cepat, dan reader yang nyaman dipakai sehari-hari.',
    ])
</head>

<body class="min-h-screen bg-base-200 font-sans text-base-content selection:bg-primary/20">
    <div @class([
        'flex min-h-screen w-full flex-col',
        'mx-auto max-w-7xl px-3 py-4 sm:px-4 lg:px-5' => !(
            ($fullWidthLayout ?? false) ===
            true
        ),
        'px-0 py-0' => ($fullWidthLayout ?? false) === true,
    ])>
        @unless (($hideNavbar ?? false) === true)
            @include('layouts.partials.navbar')
        @endunless

        @isset($header)
            <header class="mb-6 rounded-[1.75rem] border border-base-300/70 bg-base-100/80 px-5 py-4 shadow-lg">
                {{ $header }}
            </header>
        @endisset

        <main class="flex-1 space-y-6">
            @unless (($hideAlerts ?? false) === true)
                @include('layouts.partials.alerts')
            @endunless

            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        @unless (($hideFooter ?? false) === true)
            @include('layouts.partials.footer')
        @endunless
    </div>

    <script>
        function searchComponent() {
            return {
                query: '',
                results: [],
                open: false,
                loading: false,
                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;

                    try {
                        let res = await fetch(`/search/comics?q=${encodeURIComponent(this.query)}`);
                        this.results = await res.json();
                    } catch (e) {
                        this.results = [];
                    }
                    this.loading = false;
                }
            };
        }
    </script>
</body>

</html>
