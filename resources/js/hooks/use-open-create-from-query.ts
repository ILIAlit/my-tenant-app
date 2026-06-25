import { useEffect } from 'react';

export function useOpenCreateFromQuery(openCreate: () => void): void {
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);

        if (params.get('create') === '1') {
            openCreate();
        }
    }, [openCreate]);
}
