import { usePage } from '@inertiajs/react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import NotificationBell from '@/components/notifications/notification-bell';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Role } from '@/enum/auth';
import type { BreadcrumbItem as BreadcrumbItemType, Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const page = usePage<PageProps>();
    const { user } = page.props.auth;

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                {user.role === Role.Admin ? (
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                ) : (
                    <h1 className="text-lg font-semibold">
                        {user.last_name} {user.name} {user.middle_name}
                    </h1>
                )}
            </div>
            <NotificationBell />
        </header>
    );
}
