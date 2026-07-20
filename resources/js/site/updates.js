export function applyUpdateState(section) {
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
            ? 'grid gap-4 lg:grid-cols-2 xl:grid-cols-3'
            : 'grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6';
    }

    let visibleCount = 0;

    items.forEach((item) => {
        const matchFilter = filter === 'all' || item.dataset.updateSource === filter;
        item.classList.toggle('hidden', !matchFilter);

        if (matchFilter) {
            visibleCount += 1;
        }

        const link = item.querySelector('[data-update-link]');
        const card = item.querySelector('[data-update-card]');
        const image = item.querySelector('[data-update-image]');
        const content = item.querySelector('[data-update-content]');

        if (!card || !link || !image || !content) {
            return;
        }

        item.classList.remove('space-y-0', 'space-y-3');

        if (layout === 'list') {
            item.classList.add('space-y-0');
            card.className = 'flex items-start gap-4 overflow-visible border-0 bg-transparent shadow-none transition hover:-translate-y-1';
            link.className = 'block w-28 shrink-0 sm:w-32';
            image.className = 'aspect-[3/4] w-full rounded-[1.1rem] object-cover shadow-lg';
            content.className = 'flex min-w-0 flex-1 flex-col gap-3 px-0 py-1';
        } else {
            item.classList.add('space-y-3');
            card.className = 'overflow-hidden rounded-[1.4rem] border border-base-300/70 bg-base-100/55 shadow-lg transition hover:-translate-y-1';
            link.className = 'block';
            image.className = 'aspect-[4/5] w-full object-cover';
            content.className = 'space-y-3 p-3';
        }
    });

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount > 0);
    }
}

export function initUpdateSections() {
    document.querySelectorAll('[data-update-section]').forEach((section) => {
        if (!section.dataset.activeFilter) {
            section.dataset.activeFilter = 'project';
        }

        if (!section.dataset.activeLayout) {
            section.dataset.activeLayout = 'grid';
        }

        applyUpdateState(section);
    });

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

        if (!layoutButton) {
            return;
        }

        const section = layoutButton.closest('[data-update-section]');

        if (!section) {
            return;
        }

        section.dataset.activeLayout = layoutButton.dataset.updateLayout;
        applyUpdateState(section);
    });
}
