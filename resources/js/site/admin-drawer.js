import { adminDrawerQuery, adminDrawerStorageKey } from './config';

export function syncAdminDrawerState(checkbox) {
    const expanded = localStorage.getItem(adminDrawerStorageKey) === 'expanded';
    checkbox.checked = adminDrawerQuery.matches ? expanded : false;
}

export function initAdminDrawers() {
    document.querySelectorAll('[data-admin-drawer]').forEach((checkbox) => {
        syncAdminDrawerState(checkbox);
    });

    document.addEventListener('change', (event) => {
        const adminDrawer = event.target.closest('[data-admin-drawer]');

        if (!adminDrawer) {
            return;
        }

        if (adminDrawerQuery.matches) {
            localStorage.setItem(adminDrawerStorageKey, adminDrawer.checked ? 'expanded' : 'collapsed');
        }
    });

    adminDrawerQuery.addEventListener('change', () => {
        document.querySelectorAll('[data-admin-drawer]').forEach((checkbox) => {
            syncAdminDrawerState(checkbox);
        });
    });
}
