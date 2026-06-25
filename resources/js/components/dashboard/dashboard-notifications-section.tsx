import { Link, router } from '@inertiajs/react';
import { CheckCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import notifications from '@/routes/notifications';
import type { DashboardNotificationsFeed } from '@/types';

type DashboardNotificationsSectionProps = {
    feed: DashboardNotificationsFeed;
    fitContent?: boolean;
    compact?: boolean;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });

export default function DashboardNotificationsSection({
    feed,
    fitContent = false,
    compact = false,
}: DashboardNotificationsSectionProps) {
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
        <DashboardSection
            title="Уведомления"
            fitContent={fitContent}
            compact={compact}
            description={
                feed.unread_count > 0
                    ? `${feed.unread_count} непрочитанных`
                    : 'Последние события'
            }
            action={
                feed.unread_count > 0 ? (
                    <Button
                        variant="outline"
                        size="sm"
                        type="button"
                        className={cn(
                            'h-8 px-2 text-xs',
                            compact && 'h-6 px-1.5 text-[10px]',
                        )}
                        onClick={markAllAsRead}
                    >
                        <CheckCheck className="size-3.5" />
                        Все
                    </Button>
                ) : undefined
            }
        >
            {feed.items.length === 0 ? (
                <p className="text-muted-foreground text-xs">
                    Уведомлений пока нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={fitContent}>
                    <div className={compact ? 'space-y-1' : 'space-y-2'}>
                        {feed.items.map((item) => (
                            <div
                                key={item.id}
                                className={cn(
                                    'rounded-md border px-2',
                                    compact ? 'py-1' : 'py-1.5',
                                    item.read_at
                                        ? 'bg-background'
                                        : 'border-primary/20 bg-primary/5',
                                )}
                            >
                                <div className="flex items-start justify-between gap-2">
                                    <p className="text-sm font-medium">
                                        {item.title}
                                    </p>
                                    <span className="text-muted-foreground shrink-0 text-[10px]">
                                        {formatDate(item.created_at)}
                                    </span>
                                </div>
                                <p className="text-muted-foreground mt-0.5 line-clamp-2 text-xs">
                                    {item.message}
                                </p>
                                <div className="mt-1 flex gap-2">
                                    {item.url && (
                                        <Link
                                            href={item.url}
                                            className="text-primary text-xs hover:underline"
                                            onClick={() => {
                                                if (!item.read_at) {
                                                    markAsRead(item.id);
                                                }
                                            }}
                                        >
                                            Открыть
                                        </Link>
                                    )}
                                    {!item.read_at && (
                                        <button
                                            type="button"
                                            onClick={() => markAsRead(item.id)}
                                            className="text-muted-foreground text-xs hover:underline"
                                        >
                                            Прочитано
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
