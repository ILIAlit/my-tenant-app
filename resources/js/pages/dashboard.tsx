import { Head, usePage } from '@inertiajs/react';
import {
    Building2,
    TrendingDown,
    TrendingUp,
    Users,
    Wallet,
} from 'lucide-react';
import type { ComponentType, ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import HousePlanSection from '@/components/house-plan/house-plan-section';
import DashboardNewsSection from '@/components/dashboard/dashboard-news-section';
import DashboardNotificationsSection from '@/components/dashboard/dashboard-notifications-section';
import FinancialChartSection from '@/components/dashboard/financial-chart-section';
import MonthlyExpensesSection from '@/components/dashboard/monthly-expenses-section';
import QuickActionsSection from '@/components/dashboard/quick-actions-section';
import RecentMeterReadingsSection from '@/components/dashboard/recent-meter-readings-section';
import RecentPaymentsSection from '@/components/dashboard/recent-payments-section';
import RentersWithDebtSection from '@/components/dashboard/renters-with-debt-section';
import RenterDashboardView from '@/components/renter-dashboard/renter-dashboard-view';
import { dashboard } from '@/routes';
import type {
    Auth,
    DashboardFinancialChart,
    DashboardNotificationsFeed,
    DashboardNewsItem,
    DashboardRecentMeterReading,
    DashboardMonthlyExpenses,
    DashboardRenterWithDebt,
    DashboardStatistics,
    HousePlan,
    PaymentListItem,
    RenterDashboard,
} from '@/types';

type PageProps = {
    auth: Auth;
    statistics: DashboardStatistics | null;
    housePlan: HousePlan | null;
    monthlyExpenses: DashboardMonthlyExpenses | null;
    recentPayments: PaymentListItem[] | null;
    recentMeterReadings: DashboardRecentMeterReading[] | null;
    rentersWithDebt: DashboardRenterWithDebt[] | null;
    financialChart: DashboardFinancialChart | null;
    dashboardNotifications: DashboardNotificationsFeed | null;
    dashboardNews: DashboardNewsItem[] | null;
    renterDashboard: RenterDashboard | null;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

type StatCardProps = {
    title: string;
    value: string;
    description: string;
    icon: ComponentType<{ className?: string }>;
    iconClassName: string;
    valueClassName?: string;
};

function StatCard({
    title,
    value,
    description,
    icon: Icon,
    iconClassName,
    valueClassName,
}: StatCardProps) {
    return (
        <Card className="gap-0 py-0 shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-3 py-2">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div
                    className={`flex size-7 shrink-0 items-center justify-center rounded-md ${iconClassName}`}
                >
                    <Icon className="size-4" />
                </div>
            </CardHeader>
            <CardContent className="px-3 pb-3 pt-0">
                <div
                    className={`text-lg font-bold leading-tight xl:text-xl ${valueClassName ?? ''}`}
                >
                    {value}
                </div>
                <CardDescription className="mt-0.5 line-clamp-2 text-xs">
                    {description}
                </CardDescription>
            </CardContent>
        </Card>
    );
}

function PaymentProgressStatCard({
    paidCount,
    totalCount,
}: {
    paidCount: number;
    totalCount: number;
}) {
    const percentage =
        totalCount > 0 ? Math.round((paidCount / totalCount) * 100) : 0;
    const circumference = 2 * Math.PI * 15.915;
    const dashOffset = circumference - (percentage / 100) * circumference;

    return (
        <Card className="gap-0 py-0 shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-3 py-2">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                    Оплата
                </CardTitle>
            </CardHeader>
            <CardContent className="flex items-center gap-3 px-3 pb-3 pt-0">
                <div className="relative size-14 shrink-0">
                    <svg
                        viewBox="0 0 36 36"
                        className="size-14 -rotate-90"
                        aria-hidden
                    >
                        <circle
                            cx="18"
                            cy="18"
                            r="15.915"
                            fill="transparent"
                            stroke="currentColor"
                            strokeWidth="3"
                            className="text-muted/30"
                        />
                        <circle
                            cx="18"
                            cy="18"
                            r="15.915"
                            fill="transparent"
                            stroke="currentColor"
                            strokeWidth="3"
                            strokeDasharray={`${circumference} ${circumference}`}
                            strokeDashoffset={dashOffset}
                            strokeLinecap="round"
                            className="text-green-600 transition-all"
                        />
                    </svg>
                    <span className="absolute inset-0 flex items-center justify-center text-xs font-bold">
                        {percentage}%
                    </span>
                </div>
                <div className="min-w-0">
                    <p className="text-sm font-semibold leading-tight">
                        {paidCount} из {totalCount}
                    </p>
                    <CardDescription className="mt-0.5 text-xs">
                        комнат без долга
                    </CardDescription>
                </div>
            </CardContent>
        </Card>
    );
}

function DashboardPanel({
    children,
    className = '',
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={`min-h-0 ${className}`.trim()}>{children}</div>
    );
}

function formatAdminName(auth: Auth): string {
    const { user } = auth;
    const fullName = [user.last_name, user.name, user.middle_name]
        .filter(Boolean)
        .join(' ');

    return fullName || user.name;
}

export default function Dashboard() {
    const {
        auth,
        statistics,
        housePlan,
        monthlyExpenses,
        recentPayments,
        recentMeterReadings,
        rentersWithDebt,
        financialChart,
        dashboardNotifications,
        dashboardNews,
        renterDashboard,
    } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Главная" />
            {renterDashboard ? (
                <RenterDashboardView auth={auth} data={renterDashboard} />
            ) : (
            <div className="flex flex-col gap-2 pb-4">
                {statistics ? (
                    <>
                        <div>
                            <h1 className="text-lg font-semibold sm:text-xl">
                                Добро пожаловать, {formatAdminName(auth)}!
                            </h1>
                            <p className="text-muted-foreground text-xs sm:text-sm">
                                Общая информация о доме и текущая ситуация
                            </p>
                        </div>

                        <div className="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
                            <StatCard
                                title="Общий доход"
                                value={formatAmount(statistics.total_income)}
                                description="Сумма оплаченных начислений"
                                icon={TrendingUp}
                                iconClassName="bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400"
                                valueClassName="text-green-700 dark:text-green-400"
                            />
                            <StatCard
                                title="Общие расходы"
                                value={formatAmount(statistics.total_expenses)}
                                description="Сумма всех расходов"
                                icon={TrendingDown}
                                iconClassName="bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400"
                                valueClassName="text-red-700 dark:text-red-400"
                            />
                            <StatCard
                                title="Чистая прибыль"
                                value={formatAmount(statistics.net_profit)}
                                description="Доход минус расходы"
                                icon={Wallet}
                                iconClassName="bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400"
                                valueClassName="text-blue-700 dark:text-blue-400"
                            />
                            <StatCard
                                title="Задолженность"
                                value={formatAmount(statistics.total_debts)}
                                description={`${statistics.debtors_count} ${statistics.debtors_count === 1 ? 'арендатор' : statistics.debtors_count < 5 ? 'арендатора' : 'арендаторов'}`}
                                icon={Users}
                                iconClassName="bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-400"
                                valueClassName="text-orange-700 dark:text-orange-400"
                            />
                            <PaymentProgressStatCard
                                paidCount={statistics.paid_rooms_count}
                                totalCount={statistics.occupied_rooms_count}
                            />
                            <StatCard
                                title="Свободно"
                                value={String(statistics.free_rooms_count)}
                                description="Комнат «Свободна»"
                                icon={Building2}
                                iconClassName="bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400"
                            />
                        </div>

                        <div className="grid gap-2 xl:grid-cols-12 xl:items-start xl:gap-3">
                            <div className="flex flex-col gap-2 max-xl:contents xl:col-span-9 xl:grid xl:max-h-[720px] xl:grid-rows-[minmax(0,210px)_minmax(0,1fr)] xl:gap-2">
                                {housePlan && (
                                    <DashboardPanel className="flex min-h-0 flex-col overflow-hidden">
                                        <HousePlanSection
                                            housePlan={housePlan}
                                            compact
                                        />
                                    </DashboardPanel>
                                )}

                                <div className="grid min-h-0 gap-2 md:grid-cols-2 max-xl:contents xl:grid-cols-2 xl:grid-rows-2 xl:gap-2">
                                    {recentPayments && (
                                        <DashboardPanel className="flex min-h-0 flex-col overflow-hidden xl:h-full">
                                            <RecentPaymentsSection
                                                recentPayments={recentPayments}
                                                compact
                                            />
                                        </DashboardPanel>
                                    )}

                                    {recentMeterReadings && (
                                        <DashboardPanel className="flex min-h-0 flex-col overflow-hidden xl:h-full">
                                            <RecentMeterReadingsSection
                                                recentMeterReadings={
                                                    recentMeterReadings
                                                }
                                                compact
                                            />
                                        </DashboardPanel>
                                    )}

                                    {dashboardNotifications && (
                                        <DashboardPanel className="flex min-h-0 flex-col overflow-hidden xl:h-full">
                                            <DashboardNotificationsSection
                                                feed={dashboardNotifications}
                                                compact
                                            />
                                        </DashboardPanel>
                                    )}

                                    {dashboardNews && (
                                        <DashboardPanel className="flex min-h-0 flex-col overflow-hidden xl:h-full">
                                            <DashboardNewsSection
                                                newsItems={dashboardNews}
                                                compact
                                            />
                                        </DashboardPanel>
                                    )}
                                </div>
                            </div>

                            <div className="flex flex-col gap-2 max-xl:contents xl:col-span-3 xl:self-start">
                                {financialChart && (
                                    <DashboardPanel>
                                        <FinancialChartSection
                                            financialChart={financialChart}
                                            compact
                                        />
                                    </DashboardPanel>
                                )}

                                {monthlyExpenses && (
                                    <DashboardPanel>
                                        <MonthlyExpensesSection
                                            monthlyExpenses={monthlyExpenses}
                                            compact
                                        />
                                    </DashboardPanel>
                                )}

                                {rentersWithDebt && (
                                    <DashboardPanel>
                                        <RentersWithDebtSection
                                            rentersWithDebt={rentersWithDebt}
                                            compact
                                        />
                                    </DashboardPanel>
                                )}

                                <DashboardPanel>
                                    <QuickActionsSection compact />
                                </DashboardPanel>
                            </div>
                        </div>
                    </>
                ) : (
                    <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                        <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                    </div>
                )}
            </div>
            )}
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Главная',
            href: dashboard(),
        },
    ],
};
