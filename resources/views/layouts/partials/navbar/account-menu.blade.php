@auth
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button"
            class="btn btn-circle btn-sm border border-base-300/70 bg-base-100/80 relative"
            aria-label="Buka menu akun">
            @if (($unreadNotificationCount ?? 0) > 0)
                <span class="absolute right-0 top-0 h-2.5 w-2.5 rounded-full bg-success ring-2 ring-base-100"></span>
            @endif
            <span
                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
        </div>
        <ul tabindex="0"
            class="menu menu-sm dropdown-content z-[1] mt-3 w-56 rounded-box border border-base-300/70 bg-base-100 p-2 shadow-2xl">
            <li class="menu-title px-2 py-1">
                <span class="truncate">{{ auth()->user()->name }}</span>
            </li>
            <li>
                <a href="{{ route('messages') }}" class="flex items-center justify-between gap-3">
                    <span>Pesan</span>
                    @if (($unreadNotificationCount ?? 0) > 0)
                        <span class="badge badge-success badge-sm text-white">{{ $unreadNotificationCount }}</span>
                    @endif
                </a>
            </li>

            {{-- @if (($recentNotifications ?? collect())->isNotEmpty())
                <li class="menu-title px-2 pt-3">
                    <span>Notifikasi terbaru</span>
                </li>
                @foreach ($recentNotifications as $notification)
                    @php($data = $notification->data)
                    <li>
                        <a href="{{ $data['url'] ?? route('messages') }}"
                            class="block whitespace-normal leading-6">
                            <span class="font-medium">{{ $data['title'] ?? 'Notifikasi baru' }}</span>
                            <span class="block text-xs text-base-content/60">
                                {{ $data['message'] ?? 'Ada aktivitas baru.' }}
                            </span>
                        </a>
                    </li>
                @endforeach
            @endif --}}
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
        </ul>
    </div>
@else
    <a href="{{ route('login') }}" class="btn btn-circle btn-sm border border-base-300/70 bg-base-100/80"
        aria-label="Buka login atau register">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.2 0-7 2.1-7 4.5V20h14v-1.5c0-2.4-2.8-4.5-7-4.5Z" />
        </svg>
    </a>
@endauth
