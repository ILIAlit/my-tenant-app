import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import meterReadings from '@/routes/meter-readings';
import type {
    DashboardRecentMeterReading,
    MeterReadingStatus,
    MeterType,
} from '@/types';
import { meterReadingStatusLabels, meterTypeLabels } from '@/types';

type RecentMeterReadingsSectionProps = {
    recentMeterReadings: DashboardRecentMeterReading[];
    fitContent?: boolean;
    compact?: boolean;
};

const statusBadge: Record<MeterReadingStatus, string> = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
    });

const formatValue = (type: MeterType, value: number): string => {
    const unit = type === 'electricity' ? 'кВт·ч' : 'м³';

    return `${value.toFixed(1)} ${unit}`;
};

export default function RecentMeterReadingsSection({
    recentMeterReadings,
    fitContent = false,
    compact = false,
}: RecentMeterReadingsSectionProps) {
    return (
        <DashboardSection
            title="Показания счётчиков"
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
                    <Link href={meterReadings.get()}>Все</Link>
                </Button>
            }
        >
            {recentMeterReadings.length === 0 ? (
                <p className="text-muted-foreground text-xs">
                    Показаний пока нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={fitContent}>
                    <div className={compact ? 'space-y-1' : 'space-y-2'}>
                        {recentMeterReadings.map((reading) => (
                            <div
                                key={reading.id}
                                className={cn(
                                    'flex items-start justify-between gap-2 rounded-md border px-2',
                                    compact ? 'py-1' : 'py-1.5',
                                )}
                            >
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium">
                                        {reading.renter.full_name}
                                    </p>
                                    <p className="text-muted-foreground text-[10px]">
                                        {meterTypeLabels[reading.type]} ·{' '}
                                        {formatDate(reading.reading_date)}
                                    </p>
                                </div>
                                <div className="flex shrink-0 flex-col items-end gap-0.5">
                                    <p className="text-sm font-medium">
                                        {formatValue(
                                            reading.type,
                                            reading.value,
                                        )}
                                    </p>
                                    <span
                                        className={cn(
                                            'inline-block rounded-full px-2 py-0.5 text-[10px] font-medium',
                                            statusBadge[reading.status],
                                        )}
                                    >
                                        {
                                            meterReadingStatusLabels[
                                                reading.status
                                            ]
                                        }
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
