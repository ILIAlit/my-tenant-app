import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import type { DashboardMonthlyExpenses } from '@/types';
import {
    formatDashboardMonthLabel,
    visitDashboardWithQuery,
} from '@/utils/dashboard-query';

type MonthlyExpensesSectionProps = {
    monthlyExpenses: DashboardMonthlyExpenses;
    compact?: boolean;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function MonthlyExpensesSection({
    monthlyExpenses,
    compact = false,
}: MonthlyExpensesSectionProps) {
    const handleMonthChange = (value: string) => {
        visitDashboardWithQuery({
            expense_month: value || undefined,
        });
    };

    return (
        <DashboardSection
            title={
                compact
                    ? `Расходы за ${formatDashboardMonthLabel(monthlyExpenses.month)}`
                    : 'Расходы за месяц'
            }
            description={
                compact ? undefined : formatDashboardMonthLabel(monthlyExpenses.month)
            }
            fitContent={compact}
            action={
                <div className="grid gap-1">
                    <Label htmlFor="expense_month" className="text-xs">
                        Месяц
                    </Label>
                    <Input
                        id="expense_month"
                        type="month"
                        value={monthlyExpenses.month}
                        onChange={(event) =>
                            handleMonthChange(event.target.value)
                        }
                        className={compact ? 'h-7 w-28 text-xs' : 'h-8 w-36'}
                    />
                </div>
            }
        >
            <div
                className={
                    compact
                        ? 'mb-1 shrink-0 text-sm font-bold'
                        : 'mb-2 shrink-0 rounded-md border bg-muted/30 px-3 py-2'
                }
            >
                {!compact && (
                    <p className="text-muted-foreground text-xs">Итого</p>
                )}
                <p className={compact ? 'text-base' : 'text-lg font-bold'}>
                    {formatAmount(monthlyExpenses.total_amount)}
                </p>
            </div>

            {monthlyExpenses.expense_groups.length === 0 ? (
                <p className="text-muted-foreground text-sm">
                    Расходов за выбранный месяц нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={compact}>
                    <div className="space-y-2">
                        {monthlyExpenses.expense_groups.map((group) => (
                            <div key={group.date} className="space-y-1">
                                <div className="flex items-center justify-between gap-2 text-xs">
                                    <p className="font-medium">{group.date}</p>
                                    <p className="text-muted-foreground">
                                        {formatAmount(group.total_amount)}
                                    </p>
                                </div>
                                {group.expenses.map((expense) => (
                                    <div
                                        key={expense.id}
                                        className="flex items-center justify-between gap-2 rounded-md border px-2 py-1 text-xs"
                                    >
                                        <span className="truncate">
                                            {expense.title}
                                        </span>
                                        <span className="shrink-0 font-medium">
                                            {formatAmount(expense.amount)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
