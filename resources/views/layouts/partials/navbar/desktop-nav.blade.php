<ul class="menu menu-horizontal gap-1 px-1 text-sm">
    <li><a href="{{ route('home') }}"
            class="{{ request()->routeIs('home') ? 'text-primary' : '' }}">Home</a></li>
    <li><a href="{{ route('explore') }}"
            class="{{ request()->routeIs('explore') ? 'text-primary' : '' }}">Explore</a></li>
    <li><a href="{{ route('library') }}"
            class="{{ request()->routeIs('library') ? 'text-primary' : '' }}">Library</a></li>
    <li><a href="{{ route('comics.index') }}"
            class="{{ request()->routeIs('comics.index') ? 'text-primary' : '' }}">Search</a></li>
</ul>
