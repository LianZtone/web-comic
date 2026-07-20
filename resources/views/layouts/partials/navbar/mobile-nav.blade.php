<div class="dropdown">
    <div tabindex="0" role="button" class="btn btn-ghost btn-sm lg:hidden" aria-label="Buka menu navigasi">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h8m-8 6h16" />
        </svg>
    </div>
    <ul tabindex="0"
        class="menu menu-sm dropdown-content z-[1] mt-3 w-60 rounded-box border border-base-300/70 bg-base-100 p-2 shadow-2xl">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li><a href="{{ route('explore') }}">Explore</a></li>
        <li><a href="{{ route('library') }}">Library</a></li>
        <li><a href="{{ route('comics.index') }}">Search</a></li>
        @auth
            <li><a href="{{ route('library.bookmarks') }}">Bookmark</a></li>
            <li><a href="{{ route('library.readlist') }}">Readlist</a></li>
            <li><a href="{{ route('library.history') }}">History</a></li>
            <li><a href="{{ route('profile.edit') }}">Pengaturan</a></li>
            @if (auth()->user()->is_admin)
                <li><a href="{{ route('admin.comics.index') }}">Admin Panel</a></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </li>
        @else
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register</a></li>
        @endauth
    </ul>
</div>
