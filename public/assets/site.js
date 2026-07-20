const storageKey = 'scriptoria-theme';
const root = document.documentElement;
const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

function applyTheme(theme) {
    root.dataset.theme = theme;
    root.style.colorScheme = theme;

    document.querySelectorAll('[data-theme-checkbox]').forEach((node) => {
        node.checked = theme === 'dark';
    });

    document.querySelectorAll('[data-theme-text]').forEach((node) => {
        node.textContent = theme === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('title', theme === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
    });
}

function applyUpdateState(section) {
    const filter = section.dataset.activeFilter || 'project';
    const layout = section.dataset.activeLayout || 'grid';
    const container = section.querySelector('[data-update-container]');
    const items = [...section.querySelectorAll('[data-update-item]')];
    const emptyState = section.querySelector('[data-update-empty]');

    section.querySelectorAll('[data-update-filter]').forEach((button) => {
        const active = button.dataset.updateFilter === filter;
        button.setAttribute('aria-pressed', String(active));
        button.classList.toggle('btn-primary', active);
        button.classList.toggle('btn-ghost', !active);
        button.classList.toggle('border-base-300/70', !active);
    });

    section.querySelectorAll('[data-update-layout]').forEach((button) => {
        const active = button.dataset.updateLayout === layout;
        button.setAttribute('aria-pressed', String(active));
        button.classList.toggle('border-primary/30', active);
        button.classList.toggle('bg-primary/15', active);
        button.classList.toggle('text-primary', active);
        button.classList.toggle('border-base-300/70', !active);
        button.classList.toggle('bg-base-100/60', !active);
    });

    if (container) {
        container.className = layout === 'list'
            ? 'grid gap-4 lg:grid-cols-2'
            : 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6';
    }

    let visibleCount = 0;

    items.forEach((item) => {
        const matchFilter = filter === 'all' || item.dataset.updateSource === filter;
        item.classList.toggle('hidden', !matchFilter);

        if (matchFilter) {
            visibleCount += 1;
        }

        const link = item.querySelector('[data-update-link]');
        const image = item.querySelector('[data-update-image]');
        const content = item.querySelector('[data-update-content]');

        if (!link || !image || !content) {
            return;
        }

        if (layout === 'list') {
            item.className = 'space-y-0';
            link.className = 'flex overflow-hidden rounded-[1.4rem] border border-base-300/70 bg-base-100/55 shadow-lg transition hover:-translate-y-1';
            image.className = 'h-full w-32 shrink-0 object-cover sm:w-40';
            content.className = 'flex min-w-0 flex-1 flex-col justify-between gap-3 p-4';
        } else {
            item.className = 'space-y-3';
            link.className = 'block overflow-hidden rounded-[1.4rem] border border-base-300/70 bg-base-100/55 shadow-lg transition hover:-translate-y-1';
            image.className = 'aspect-[4/5] w-full object-cover';
            content.className = 'space-y-3 p-3';
        }
    });

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount > 0);
    }
}

function initUpdateSections() {
    document.querySelectorAll('[data-update-section]').forEach((section) => {
        if (!section.dataset.activeFilter) {
            section.dataset.activeFilter = 'project';
        }

        if (!section.dataset.activeLayout) {
            section.dataset.activeLayout = 'grid';
        }

        applyUpdateState(section);
    });
}

function applyReaderState(shell) {
    const clean = shell.dataset.readerMode === 'clean';

    shell.querySelectorAll('[data-reader-chrome]').forEach((node) => {
        node.classList.toggle('opacity-0', clean);
        node.classList.toggle('pointer-events-none', clean);

        if (node.dataset.readerChromeType === 'top') {
            node.classList.toggle('-translate-y-3', clean);
        }

        if (node.dataset.readerChromeType === 'bottom') {
            node.classList.toggle('translate-y-3', clean);
        }
    });
}

function initReaderShells() {
    document.querySelectorAll('[data-reader-shell]').forEach((shell) => {
        if (!shell.dataset.readerMode) {
            shell.dataset.readerMode = 'default';
        }

        applyReaderState(shell);
        updateReaderScrollButtons(shell);
    });
}

function updateReaderScrollButtons(shell) {
    const scroller = document.scrollingElement || document.documentElement;
    const viewportHeight = scroller.clientHeight || window.innerHeight || 0;
    const scrollTop = scroller.scrollTop || 0;
    const scrollHeight = scroller.scrollHeight || 0;
    const nearTop = scrollTop <= 24;
    const nearBottom = scrollTop + viewportHeight >= scrollHeight - 24;

    shell.querySelectorAll('[data-reader-scroll-control="top"]').forEach((button) => {
        button.classList.toggle('opacity-0', nearTop);
        button.classList.toggle('pointer-events-none', nearTop);
    });

    shell.querySelectorAll('[data-reader-scroll-control="bottom"]').forEach((button) => {
        button.classList.toggle('opacity-0', nearBottom);
        button.classList.toggle('pointer-events-none', nearBottom);
    });
}

function applyReaderSearch(modal) {
    const query = (modal.querySelector('[data-reader-search]')?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    modal.querySelectorAll('[data-reader-chapter-item]').forEach((item) => {
        const matches = query === '' || (item.dataset.readerSearchText || '').includes(query);
        item.classList.toggle('hidden', !matches);

        if (matches) {
            visibleCount += 1;
        }
    });

    const emptyState = modal.querySelector('[data-reader-search-empty]');

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount > 0);
    }
}

function openReaderModal(modal) {
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('flex', 'opacity-100');
    });

    applyReaderSearch(modal);
    modal.querySelector('[data-reader-search]')?.focus();
}

function closeReaderModal(modal) {
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');

    window.setTimeout(() => {
        if (modal.classList.contains('opacity-0')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }, 180);
}

const storedTheme = localStorage.getItem(storageKey);

if (storedTheme) {
    applyTheme(storedTheme);
} else {
    applyTheme(root.dataset.theme || (mediaQuery.matches ? 'dark' : 'light'));
}

document.addEventListener('click', (event) => {
    const filterButton = event.target.closest('[data-update-filter]');

    if (filterButton) {
        const section = filterButton.closest('[data-update-section]');

        if (!section) {
            return;
        }

        section.dataset.activeFilter = filterButton.dataset.updateFilter;
        applyUpdateState(section);
        return;
    }

    const layoutButton = event.target.closest('[data-update-layout]');

    if (layoutButton) {
        const section = layoutButton.closest('[data-update-section]');

        if (!section) {
            return;
        }

        section.dataset.activeLayout = layoutButton.dataset.updateLayout;
        applyUpdateState(section);
    }
});

initUpdateSections();
initReaderShells();

document.addEventListener('change', (event) => {
    const checkbox = event.target.closest('[data-theme-checkbox]');

    if (!checkbox) {
        return;
    }

    const nextTheme = checkbox.checked ? 'dark' : 'light';
    localStorage.setItem(storageKey, nextTheme);
    applyTheme(nextTheme);
});

document.addEventListener('click', (event) => {
    const readerToggle = event.target.closest('[data-reader-toggle]');

    if (readerToggle) {
        const shell = readerToggle.closest('[data-reader-shell]');

        if (!shell) {
            return;
        }

        shell.dataset.readerMode = shell.dataset.readerMode === 'clean' ? 'default' : 'clean';
        applyReaderState(shell);
        return;
    }

    const openModalButton = event.target.closest('[data-reader-modal-open]');

    if (openModalButton) {
        const shell = openModalButton.closest('[data-reader-shell]');
        const modal = shell?.querySelector('[data-reader-modal]');

        if (!modal) {
            return;
        }

        openReaderModal(modal);
        return;
    }

    const closeModalButton = event.target.closest('[data-reader-modal-close]');

    if (closeModalButton) {
        const modal = closeModalButton.closest('[data-reader-modal]');

        if (!modal) {
            return;
        }

        closeReaderModal(modal);
        return;
    }

    const modal = event.target.closest('[data-reader-modal]');

    if (modal && event.target === modal) {
        closeReaderModal(modal);
        return;
    }

    const scrollButton = event.target.closest('[data-reader-scroll]');

    if (scrollButton) {
        const shell = scrollButton.closest('[data-reader-shell]');
        const direction = scrollButton.dataset.readerScroll;
        const target = shell?.querySelector(`[data-reader-scroll-target="${direction}"]`);

        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: direction === 'top' ? 'start' : 'end',
            });
            return;
        }

        const scroller = document.scrollingElement || document.documentElement;
        const top = direction === 'top' ? 0 : Math.max(0, scroller.scrollHeight - scroller.clientHeight);

        window.scrollTo({
            top,
            behavior: 'smooth',
        });
        return;
    }

    const readerSurface = event.target.closest('[data-reader-surface]');

    if (!readerSurface) {
        return;
    }

    if (event.target.closest('a, button')) {
        return;
    }

    const shell = readerSurface.closest('[data-reader-shell]');

    if (!shell) {
        return;
    }

    shell.dataset.readerMode = shell.dataset.readerMode === 'clean' ? 'default' : 'clean';
    applyReaderState(shell);
});

document.addEventListener('input', (event) => {
    const search = event.target.closest('[data-reader-search]');

    if (!search) {
        return;
    }

    const modal = search.closest('[data-reader-modal]');

    if (!modal) {
        return;
    }

    applyReaderSearch(modal);
});

mediaQuery.addEventListener('change', (event) => {
    if (localStorage.getItem(storageKey)) {
        return;
    }

    applyTheme(event.matches ? 'dark' : 'light');
});

window.addEventListener('scroll', () => {
    document.querySelectorAll('[data-reader-shell]').forEach((shell) => {
        updateReaderScrollButtons(shell);
    });
}, { passive: true });
