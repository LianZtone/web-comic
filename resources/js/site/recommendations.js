export function applyRecommendationState(section) {
    const filter = section.dataset.activeRecommendationFilter || 'all';
    const items = [...section.querySelectorAll('[data-recommendation-item]')];
    const emptyState = section.querySelector('[data-recommendation-empty]');

    section.querySelectorAll('[data-recommendation-filter]').forEach((button) => {
        const active = button.dataset.recommendationFilter === filter;
        button.setAttribute('aria-pressed', String(active));
        button.classList.toggle('btn-primary', active);
        button.classList.toggle('btn-ghost', !active);
        button.classList.toggle('border-base-300/70', !active);
    });

    let visibleCount = 0;

    items.forEach((item) => {
        const recommendationType = item.dataset.recommendationType || '';
        const matches = filter === 'all' || recommendationType === filter;

        item.classList.toggle('hidden', !matches);

        if (matches) {
            visibleCount += 1;
        }
    });

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount > 0);
    }
}

export function initRecommendationSections() {
    document.querySelectorAll('[data-recommendation-section]').forEach((section) => {
        if (!section.dataset.activeRecommendationFilter) {
            const defaultButton = section.querySelector('[data-recommendation-filter]');
            section.dataset.activeRecommendationFilter = defaultButton?.dataset.recommendationFilter || 'all';
        }

        applyRecommendationState(section);
    });

    document.addEventListener('click', (event) => {
        const recommendationButton = event.target.closest('[data-recommendation-filter]');

        if (!recommendationButton) {
            return;
        }

        const section = recommendationButton.closest('[data-recommendation-section]');

        if (!section) {
            return;
        }

        section.dataset.activeRecommendationFilter = recommendationButton.dataset.recommendationFilter;
        applyRecommendationState(section);
    });
}
