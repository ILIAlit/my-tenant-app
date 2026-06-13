import { Head, router, usePage } from '@inertiajs/react';
import { Bell, Check, CheckCheck, Trash2 } from 'lucide-react';
import NotificationsController from '@/actions/App/Http/Controllers/Notifications/NotificationsController';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import { cn } from '@/lib/utils';
import type { AppNotification } from '@/types';

type PageProps = {
    notifications: AppNotification[];
};

const formatDateTime = (value: string | null) =>
    value
        ? new Date(value).toLocaleString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '';

const statusBadge: Record<string, { className: string; label: string }> = {
    approved: { className: 'bg-green-100 text-green-800', label: 'Одобрено' },
    rejected: { className: 'bg-red-100 text-red-800', label: 'Отклонено' },
    review: { className: 'bg-yellow-100 text-yellow-800', label: 'На проверке' },
    due_soon: { className: 'bg-orange-100 text-orange-800', label: 'Скоро срок оплаты' },
};

export default function NotificationsPage() {
    const { notifications } = usePage<PageProps>().props;

    const unreadCount = notifications.filter((item) => !item.read_at).length;

    const open = (notification: AppNotification) => {
        const goToTarget = () => router.visit(notification.data.url);

        if (notification.read_at) {
            goToTarget();

            return;
        }

        router.put(
            NotificationsController.markAsRead.url(notification.id),
            {},
            { preserveScroll: true, preserveState: true, onFinish: goToTarget },
        );
    };

    const markRead = (notification: AppNotification) => {
        router.put(
            NotificationsController.markAsRead.url(notification.id),
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    const markAllRead = () => {
        router.put(
            NotificationsController.markAllAsRead.url(),
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    const remove = (notification: AppNotification) => {
        router.delete(NotificationsController.destroy.url(notification.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <>
            <Head title="Уведомления" />

            <div className="flex items-center justify-between">
                <PageHeader
                    title="Уведомления"
                    description="Изменения статусов платежей, показаний счётчиков и напоминания о сроках оплаты"
                />
                {unreadCount > 0 && (
                    <Button
                        variant="outline"
                        size="sm"
                        className="gap-1"
                        onClick={markAllRead}
                    >
                        <CheckCheck className="size-4" />
                        Прочитать все
                    </Button>
                )}
            </div>

            {notifications.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-200 p-12 text-center text-gray-500">
                    <Bell className="size-8 opacity-40" />
                    <p>Уведомлений пока нет</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {notifications.map((notification) => {
                        const badge = statusBadge[notification.data.status];

                        return (
                            <div
                                key={notification.id}
                                className={cn(
                                    'flex items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4',
                                    !notification.read_at &&
                                        'border-blue-200 bg-blue-50/40',
                                )}
                            >
                                <button
                                    type="button"
                                    onClick={() => open(notification)}
                                    className="min-w-0 flex-1 text-left"
                                >
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="font-medium">
                                            {notification.data.title}
                                        </span>
                                        {badge && (
                                            <span
                                                className={cn(
                                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                                    badge.className,
                                                )}
                                            >
                                                {badge.label}
                                            </span>
                                        )}
                                        {!notification.read_at && (
                                            <span className="h-2 w-2 rounded-full bg-blue-500" />
                                        )}
                                    </div>
                                    <p className="mt-1 text-sm text-gray-600">
                                        {notification.data.message}
                                    </p>
                                    <p className="mt-1 text-xs text-gray-400">
                                        {formatDateTime(notification.created_at)}
                                    </p>
                                </button>

                                <div className="flex shrink-0 items-center gap-1">
                                    {!notification.read_at && (
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8"
                                            aria-label="Отметить прочитанным"
                                            onClick={() => markRead(notification)}
                                        >
                                            <Check className="size-4" />
                                        </Button>
                                    )}
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-8 w-8 text-red-600"
                                        aria-label="Удалить"
                                        onClick={() => remove(notification)}
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );
}
