import { Link, router, usePage } from '@inertiajs/react';
import { Bell, CheckCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import notifications from '@/routes/notifications';
import type { NotificationsData } from '@/types';

type SharedProps = {
    notifications: NotificationsData | null;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export function NotificationBell() {
    const { notifications: data } = usePage<SharedProps>().props;

    if (data === null) {
        return null;
    }

    const markAsRead = (id: string) => {
        router.post(
            notifications.read.url(id),
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    const markAllAsRead = () => {
        router.post(
            notifications.readAll.url(),
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
                    className="relative"
                    aria-label="Уведомления"
                >
                    <Bell size={20} />
                    {data.unread_count > 0 && (
                        <span className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-xs font-medium text-white">
                            {data.unread_count > 9 ? '9+' : data.unread_count}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
                <DropdownMenuLabel className="flex items-center justify-between">
                    <span>Уведомления</span>
                    {data.unread_count > 0 && (
                        <button
                            type="button"
                            onClick={markAllAsRead}
                            className="inline-flex items-center gap-1 text-xs font-normal text-primary hover:underline"
                        >
                            <CheckCheck size={14} />
                            Прочитать все
                        </button>
                    )}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                {data.items.length === 0 ? (
                    <div className="px-2 py-6 text-center text-sm text-muted-foreground">
                        Нет новых уведомлений
                    </div>
                ) : (
                    data.items.map((item) => (
                        <DropdownMenuItem
                            key={item.id}
                            className="flex cursor-default flex-col items-start gap-1 p-3"
                            onSelect={(event) => event.preventDefault()}
                        >
                            <div className="flex w-full items-start justify-between gap-2">
                                <span className="font-medium">
                                    {item.title}
                                </span>
                                <span className="shrink-0 text-xs text-muted-foreground">
                                    {formatDate(item.created_at)}
                                </span>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                {item.message}
                            </p>
                            <div className="mt-1 flex gap-2">
                                {item.url && (
                                    <Link
                                        href={item.url}
                                        className="text-xs text-primary hover:underline"
                                        onClick={() => markAsRead(item.id)}
                                    >
                                        Открыть
                                    </Link>
                                )}
                                <button
                                    type="button"
                                    onClick={() => markAsRead(item.id)}
                                    className="text-xs text-muted-foreground hover:underline"
                                >
                                    Прочитано
                                </button>
                            </div>
                        </DropdownMenuItem>
                    ))
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
