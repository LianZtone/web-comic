<main class="flex-1 space-y-6">
    @unless (($hideAlerts ?? false) === true)
        @include('layouts.partials.alerts')
    @endunless
    @yield('content')
</main>
