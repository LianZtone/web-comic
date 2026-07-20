import { darkTheme, defaultTheme, mediaQuery, root, storageKey } from './config';

export function applyTheme(theme) {
    root.dataset.theme = theme;
    root.style.colorScheme = theme === darkTheme ? 'dark' : 'light';

    document.querySelectorAll('[data-theme-checkbox]').forEach((node) => {
        node.checked = theme === darkTheme;
    });

    document.querySelectorAll('[data-theme-text]').forEach((node) => {
        node.textContent = theme === darkTheme ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('title', theme === darkTheme ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
    });
}

export function initTheme() {
    const storedTheme = localStorage.getItem(storageKey);

    if (storedTheme) {
        applyTheme(storedTheme);
    } else {
        applyTheme(mediaQuery.matches ? darkTheme : defaultTheme);
    }

    document.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-theme-checkbox]');

        if (!checkbox) {
            return;
        }

        const nextTheme = checkbox.checked ? darkTheme : defaultTheme;
        localStorage.setItem(storageKey, nextTheme);
        applyTheme(nextTheme);
    });

    mediaQuery.addEventListener('change', (event) => {
        if (localStorage.getItem(storageKey)) {
            return;
        }

        applyTheme(event.matches ? darkTheme : defaultTheme);
    });
}
