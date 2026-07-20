<header class="sticky top-3 z-50 mb-5">
    <div class="navbar rounded-[1.75rem] border border-base-300/70 bg-base-100 px-4 shadow-lg">
        <div class="navbar-start">
            @include('layouts.partials.navbar.mobile-nav')
            @include('layouts.partials.navbar.brand')
        </div>

        <div class="navbar-center hidden xl:flex">
            @include('layouts.partials.navbar.desktop-nav')
        </div>

        <div class="navbar-end gap-2">
            @include('layouts.partials.navbar.search')
            @include('layouts.partials.navbar.theme-toggle')
            @include('layouts.partials.navbar.account-menu')
        </div>
    </div>
</header>
