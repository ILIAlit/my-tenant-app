import { router, usePage } from '@inertiajs/react';
import { Bell, CheckCheck } from 'lucide-react';
import NotificationsController from '@/actions/App/Http/Controllers/Notifications/NotificationsController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { AppNotification, NotificationsShared } from '@/types';

type PageProps = {
    notifications: NotificationsShared | null;
};

const formatTime = (value: string | null) =>
    value
        ? new Date(value).toLocaleString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '';

const statusAccent = (notification: AppNotification) => {
    switch (notification.data.status) {
        case 'approved':
            return 'bg-green-500';
        case 'rejected':
            return 'bg-red-500';
        case 'due_soon':
            return 'bg-orange-500';
        default:
            return 'bg-yellow-500';
    }
};

export default function NotificationBell() {
    const { notifications } = usePage<PageProps>().props;

    const items = notifications?.items ?? [];
    const unreadCount = notifications?.unread_count ?? 0;

    const openNotification = (notification: AppNotification) => {
        const goToTarget = () => router.visit(notification.data.url);

        if (notification.read_at) {
            goToTarget();

            return;
        }

        router.put(
            NotificationsController.markAsRead.url(notification.id),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: goToTarget,
            },
        );
    };

    const markAllAsRead = () => {
        router.put(
            NotificationsController.markAllAsRead.url(),
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="relative h-9 w-9 cursor-pointer"
                    aria-label="Уведомления"
                >
                    <Bell className="!size-5 opacity-80" />
                    {unreadCount > 0 && (
                        <span className="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80 p-0">
                <div className="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <span className="text-sm font-semibold">Уведомления</span>
                    {unreadCount > 0 && (
                        <button
                            type="button"
                            onClick={markAllAsRead}
                            className="flex items-center gap-1 text-xs text-primary hover:underline"
                        >
                            <CheckCheck className="size-3.5" />
                            Прочитать все
                        </button>
                    )}
                </div>

                <div className="max-h-96 overflow-y-auto">
                    {items.length === 0 ? (
                        <p className="px-4 py-8 text-center text-sm text-gray-500">
                            Уведомлений пока нет
                        </p>
                    ) : (
                        items.map((notification) => (
                            <button
                                key={notification.id}
                                type="button"
                                onClick={() => openNotification(notification)}
                                className={cn(
                                    'flex w-full gap-3 border-b border-gray-50 px-4 py-3 text-left transition-colors hover:bg-gray-50',
                                    !notification.read_at && 'bg-blue-50/40',
                                )}
                            >
                                <span
                                    className={cn(
                                        'mt-1.5 h-2 w-2 shrink-0 rounded-full',
                                        notification.read_at
                                            ? 'bg-transparent'
                                            : statusAccent(notification),
                                    )}
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium">
                                        {notification.data.title}
                                    </p>
                                    <p className="mt-0.5 line-clamp-2 text-xs text-gray-600">
                                        {notification.data.message}
                                    </p>
                                    <p className="mt-1 text-[11px] text-gray-400">
                                        {formatTime(notification.created_at)}
                                    </p>
                                </div>
                            </button>
                        ))
                    )}
                </div>

                <a
                    href={NotificationsController.index.url()}
                    className="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-medium text-primary hover:underline"
                >
                    Все уведомления
                </a>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
