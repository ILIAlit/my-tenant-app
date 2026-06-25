import { Link } from '@inertiajs/react';
import { useState } from 'react';
import ViewNewsModal from '@/components/news/view-news-modal';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import news from '@/routes/news';
import type { DashboardNewsItem } from '@/types';

type DashboardNewsSectionProps = {
    newsItems: DashboardNewsItem[];
    fitContent?: boolean;
    compact?: boolean;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: 'short',
    });

export default function DashboardNewsSection({
    newsItems,
    fitContent = false,
    compact = false,
}: DashboardNewsSectionProps) {
    const [selectedNews, setSelectedNews] = useState<DashboardNewsItem | null>(
        null,
    );
    const [viewOpen, setViewOpen] = useState(false);

    const openNews = (item: DashboardNewsItem) => {
        setSelectedNews(item);
        setViewOpen(true);
    };

    return (
        <>
            <DashboardSection
                title="Объявления"
                description={compact ? undefined : 'Последние новости'}
                fitContent={fitContent}
                compact={compact}
                action={
                    <Button
                        variant="outline"
                        size="sm"
                        className={cn(
                            'h-8 px-2 text-xs',
                            compact && 'h-6 px-1.5 text-[10px]',
                        )}
                        asChild
                    >
                        <Link href={news.get()}>Все</Link>
                    </Button>
                }
            >
                {newsItems.length === 0 ? (
                    <p className="text-muted-foreground text-xs">
                        Объявлений пока нет
                    </p>
                ) : (
                    <DashboardSectionScroll fitContent={fitContent}>
                        <div className={compact ? 'space-y-1' : 'space-y-2'}>
                            {newsItems.map((item) => (
                                <button
                                    key={item.id}
                                    type="button"
                                    onClick={() => openNews(item)}
                                    className={cn(
                                        'hover:bg-muted/50 w-full rounded-md border px-2 text-left transition-colors',
                                        compact ? 'py-1' : 'py-1.5',
                                    )}
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="truncate text-sm font-medium">
                                            {item.title}
                                        </p>
                                        <span className="text-muted-foreground shrink-0 text-[10px]">
                                            {formatDate(item.date)}
                                        </span>
                                    </div>
                                    <p className="text-muted-foreground mt-0.5 line-clamp-1 text-xs">
                                        {item.text}
                                    </p>
                                </button>
                            ))}
                        </div>
                    </DashboardSectionScroll>
                )}
            </DashboardSection>

            <ViewNewsModal
                news={selectedNews}
                open={viewOpen}
                onOpenChange={setViewOpen}
            />
        </>
    );
}
