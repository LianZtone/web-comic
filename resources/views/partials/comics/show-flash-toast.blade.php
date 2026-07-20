@if ($comicToastType && $comicToastMessage)
    <div class="pointer-events-none fixed inset-x-0 top-5 z-[75] flex justify-center px-4">
        <div class="pointer-events-auto" data-flash-toast data-flash-toast-duration="5000">
            <div @class([
                'flex items-center gap-3 rounded-full border bg-base-100 px-4 py-3 shadow-2xl',
                'border-success/25' => $comicToastType === 'success',
                'border-warning/25' => $comicToastType === 'warning',
            ])>
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-full',
                    'bg-success/15 text-success' => $comicToastType === 'success',
                    'bg-warning/15 text-warning' => $comicToastType === 'warning',
                ])>
                    @if ($comicToastType === 'success')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                    @endif
                </span>
                <div class="text-sm font-medium text-base-content">{{ $comicToastMessage }}</div>
            </div>
        </div>
    </div>
@endif
