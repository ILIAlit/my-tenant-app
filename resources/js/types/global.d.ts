import type { Auth } from '@/types/auth';
import type { NotificationsShared } from '@/types/notifications/notifications';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            notifications: NotificationsShared | null;
            [key: string]: unknown;
        };
    }
}
