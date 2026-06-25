import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import type { DashboardFinancialChart } from '@/types';
import type { ChargeCategory } from '@/types';
import { chargeCategoryChartColors } from '@/types';
import {
    formatDashboardMonthLabel,
    visitDashboardWithQuery,
} from '@/utils/dashboard-query';

type FinancialChartSectionProps = {
    financialChart: DashboardFinancialChart;
    compact?: boolean;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

type DonutSegment = {
    color: string;
    percentage: number;
};

function DonutChart({
    segments,
    className,
}: {
    segments: DonutSegment[];
    className?: string;
}) {
    let cumulative = 0;

    return (
        <svg
            viewBox="0 0 36 36"
            className={cn(
                'size-28 shrink-0 -rotate-90 xl:size-32',
                className,
            )}
            aria-hidden
        >
            <circle
                cx="18"
                cy="18"
                r="15.915"
                fill="transparent"
                stroke="currentColor"
                strokeWidth="3.8"
                className="text-muted/30"
            />
            {segments.map((segment, index) => {
                const dashOffset = 25 - cumulative;

                cumulative += segment.percentage;

                return (
                    <circle
                        key={index}
                        cx="18"
                        cy="18"
                        r="15.915"
                        fill="transparent"
                        stroke={segment.color}
                        strokeWidth="3.8"
                        strokeDasharray={`${segment.percentage} ${100 - segment.percentage}`}
                        strokeDashoffset={dashOffset}
                    />
                );
            })}
        </svg>
    );
}

export default function FinancialChartSection({
    financialChart,
    compact = false,
}: FinancialChartSectionProps) {
    const handleMonthChange = (value: string) => {
        visitDashboardWithQuery({
            finance_month: value || undefined,
        });
    };

    const segments = financialChart.categories.map((item) => ({
        color: chargeCategoryChartColors[item.category as ChargeCategory],
        percentage: item.percentage,
    }));

    return (
        <DashboardSection
            title="Финансовый обзор"
            fitContent={compact}
            description={
                compact
                    ? formatDashboardMonthLabel(financialChart.month)
                    : `Начисления за ${formatDashboardMonthLabel(financialChart.month)}`
            }
            action={
                <div className="grid gap-1">
                    <Label htmlFor="finance_month" className="text-xs">
                        Месяц
                    </Label>
                    <Input
                        id="finance_month"
                        type="month"
                        value={financialChart.month}
                        onChange={(event) =>
                            handleMonthChange(event.target.value)
                        }
                        className={compact ? 'h-7 w-28 text-xs' : 'h-8 w-36'}
                    />
                </div>
            }
        >
            {!compact && (
                <div className="mb-2 shrink-0 rounded-md border bg-muted/30 px-3 py-2">
                    <p className="text-muted-foreground text-xs">Итого</p>
                    <p className="text-lg font-bold">
                        {formatAmount(financialChart.total_amount)}
                    </p>
                </div>
            )}

            {financialChart.categories.length === 0 ? (
                <p className="text-muted-foreground text-sm">
                    Начислений за выбранный месяц нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={compact}>
                    <div
                        className={
                            compact
                                ? 'flex flex-col items-center gap-2'
                                : 'flex flex-col gap-3 lg:flex-row lg:items-start'
                        }
                    >
                        <div className="relative mx-auto flex shrink-0 items-center justify-center lg:mx-0">
                            <DonutChart
                                segments={segments}
                                className={
                                    compact ? 'size-24' : 'size-28 xl:size-32'
                                }
                            />
                            <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-muted-foreground text-[10px]">
                                    Всего
                                </span>
                                <span className="text-xs font-semibold">
                                    {formatAmount(financialChart.total_amount)}
                                </span>
                            </div>
                        </div>

                        <div className="min-w-0 w-full flex-1 space-y-1.5">
                            {financialChart.categories.map((item) => (
                                <div key={item.category} className="space-y-1">
                                    <div className="flex items-center justify-between gap-2 text-xs">
                                        <div className="flex min-w-0 items-center gap-1.5">
                                            <span
                                                className="size-2.5 shrink-0 rounded-full"
                                                style={{
                                                    backgroundColor:
                                                        chargeCategoryChartColors[
                                                            item.category as ChargeCategory
                                                        ],
                                                }}
                                            />
                                            <span className="truncate">
                                                {item.label}
                                            </span>
                                        </div>
                                        <span className="shrink-0 font-medium">
                                            {formatAmount(item.amount)}
                                        </span>
                                    </div>
                                    <div className="bg-muted h-1.5 overflow-hidden rounded-full">
                                        <div
                                            className="h-full rounded-full"
                                            style={{
                                                width: `${item.percentage}%`,
                                                backgroundColor:
                                                    chargeCategoryChartColors[
                                                        item.category as ChargeCategory
                                                    ],
                                            }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
