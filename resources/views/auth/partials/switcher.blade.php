<div class="mt-6 flex rounded-[1.2rem] border border-base-300/70 bg-base-200/55 p-1">
    <a href="{{ route('login') }}"
        @class([
            'flex-1 rounded-[0.95rem] px-4 py-2 text-center text-sm font-semibold transition',
            'bg-primary text-primary-content shadow-sm' => request()->routeIs('login'),
            'text-base-content/65 hover:bg-base-100/80' => !request()->routeIs('login'),
        ])>
        Login
    </a>
    <a href="{{ route('register') }}"
        @class([
            'flex-1 rounded-[0.95rem] px-4 py-2 text-center text-sm font-semibold transition',
            'bg-primary text-primary-content shadow-sm' => request()->routeIs('register'),
            'text-base-content/65 hover:bg-base-100/80' => !request()->routeIs('register'),
        ])>
        Register
    </a>



</div>
