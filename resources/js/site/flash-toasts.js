export function initFlashToasts() {
    document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
        const bar = toast.querySelector('[data-flash-toast-bar]');
        const duration = Number(toast.dataset.flashToastDuration || 5000);

        if (bar) {
            bar.animate(
                [
                    { transform: 'scaleX(1)', transformOrigin: 'left' },
                    { transform: 'scaleX(0)', transformOrigin: 'left' },
                ],
                {
                    duration,
                    easing: 'linear',
                    fill: 'forwards',
                },
            );
        }

        window.setTimeout(() => {
            toast.remove();
        }, duration);
    });

    document.addEventListener('click', (event) => {
        const flashToastClose = event.target.closest('[data-flash-toast-close]');

        if (!flashToastClose) {
            return;
        }

        flashToastClose.closest('[data-flash-toast]')?.remove();
    });
}
