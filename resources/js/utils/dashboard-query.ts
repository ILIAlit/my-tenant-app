import { router } from '@inertiajs/react';
import { dashboard } from '@/routes';

export function buildDashboardQuery(
    updates: Record<string, string | undefined>,
): Record<string, string> {
    const params = new URLSearchParams(window.location.search);

    Object.entries(updates).forEach(([key, value]) => {
        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }
    });

    return Object.fromEntries(params.entries());
}

export function visitDashboardWithQuery(
    updates: Record<string, string | undefined>,
): void {
    router.get(
        dashboard.url({ query: buildDashboardQuery(updates) }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

export const formatDashboardMonthLabel = (month: string): string => {
    const [year, monthNum] = month.split('-');

    return new Date(Number(year), Number(monthNum) - 1, 1).toLocaleDateString(
        'ru-RU',
        {
            month: 'long',
            year: 'numeric',
        },
    );
};
