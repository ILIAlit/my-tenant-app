import { Head, Link, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowRight,
    CreditCard,
    FileText,
    Megaphone,
    Wallet,
} from 'lucide-react';
import AdminDashboard from '@/components/dashboard/admin-dashboard';
import PageHeader from '@/components/ui/page-header';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import invoices from '@/routes/invoices';
import news from '@/routes/news';
import payments from '@/routes/payments';
import type {
    AdminDashboardStats,
    DashboardLastPayment,
    DashboardStats,
    DashboardUnpaidInvoice,
} from '@/types/dashboard/dashboard';
import type { InvoiceStatus } from '@/types/invoices/invoices';
import type { PaymentStatus } from '@/types/payments/payments';

type PageProps = {
    stats: DashboardStats | null;
    adminStats: AdminDashboardStats | null;
};

const formatMoney = (value: number) =>
    `${new Intl.NumberFormat('ru-RU').format(value)} ₽`;

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '';

const invoiceBadge: Record<InvoiceStatus, { className: string; label: string }> =
    {
        debt: { className: 'bg-red-100 text-red-800', label: 'Долг' },
        review: { className: 'bg-yellow-100 text-yellow-800', label: 'На проверке' },
        paid: { className: 'bg-green-100 text-green-800', label: 'Оплачено' },
    };

const paymentBadge: Record<PaymentStatus, { className: string; label: string }> =
    {
        review: { className: 'bg-yellow-100 text-yellow-800', label: 'На проверке' },
        approved: { className: 'bg-green-100 text-green-800', label: 'Одобрен' },
        rejected: { className: 'bg-red-100 text-red-800', label: 'Отклонён' },
    };

function StatCard({
    icon,
    label,
    value,
    hint,
    accent,
}: {
    icon: React.ReactNode;
    label: string;
    value: string;
    hint?: string;
    accent: string;
}) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5">
            <div className="flex items-center gap-3">
                <span
                    className={cn(
                        'flex size-10 items-center justify-center rounded-lg',
                        accent,
                    )}
                >
                    {icon}
                </span>
                <span className="text-sm font-medium text-gray-500">{label}</span>
            </div>
            <p className="mt-4 text-2xl font-semibold text-gray-900">{value}</p>
            {hint && <p className="mt-1 text-sm text-gray-500">{hint}</p>}
        </div>
    );
}

function LastPaymentValue({ payment }: { payment: DashboardLastPayment | null }) {
    if (!payment) {
        return <span className="text-gray-400">Нет платежей</span>;
    }

    return <>{formatMoney(payment.amount)}</>;
}

export default function Dashboard() {
    const { stats, adminStats } = usePage<PageProps>().props;

    if (adminStats) {
        return (
            <>
                <Head title="Главная" />
                <PageHeader
                    title="Главная"
                    description="Сводка по дому, платежам и коммунальным услугам"
                />
                <AdminDashboard stats={adminStats} />
            </>
        );
    }

    if (!stats) {
        return (
            <>
                <Head title="Главная" />
                <PageHeader title="Главная" description="Сводка" />
            </>
        );
    }

    const { lastPayment, unpaidInvoices, news: newsItems } = stats;

    return (
        <>
            <Head title="Главная" />

            <PageHeader
                title="Главная"
                description="Сводка по вашей аренде"
            />

            <div className="mt-6 grid gap-4 md:grid-cols-3">
                <StatCard
                    icon={<AlertCircle className="size-5 text-red-600" />}
                    label="Задолженность"
                    value={formatMoney(stats.totalDebt)}
                    hint={
                        stats.totalDebt > 0
                            ? 'Есть просроченные начисления'
                            : 'Просроченных начислений нет'
                    }
                    accent="bg-red-100"
                />
                <StatCard
                    icon={<Wallet className="size-5 text-amber-600" />}
                    label="К оплате"
                    value={formatMoney(stats.totalUnpaid)}
                    hint={`Неоплаченных начислений: ${stats.unpaidCount}`}
                    accent="bg-amber-100"
                />
                <div className="rounded-xl border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-green-100">
                            <CreditCard className="size-5 text-green-600" />
                        </span>
                        <span className="text-sm font-medium text-gray-500">
                            Последняя оплата
                        </span>
                    </div>
                    <p className="mt-4 text-2xl font-semibold text-gray-900">
                        <LastPaymentValue payment={lastPayment} />
                    </p>
                    {lastPayment && (
                        <div className="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span>{formatDate(lastPayment.created_at)}</span>
                            <span
                                className={cn(
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    paymentBadge[lastPayment.status]?.className,
                                )}
                            >
                                {paymentBadge[lastPayment.status]?.label}
                            </span>
                        </div>
                    )}
                </div>
            </div>

            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <section className="rounded-xl border border-gray-200 bg-white">
                    <div className="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <div className="flex items-center gap-2">
                            <FileText className="size-5 text-gray-500" />
                            <h2 className="font-semibold">
                                Неоплаченные начисления
                            </h2>
                        </div>
                        <Link
                            href={invoices.get()}
                            className="flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                        >
                            Все
                            <ArrowRight className="size-4" />
                        </Link>
                    </div>

                    {unpaidInvoices.length === 0 ? (
                        <div className="px-5 py-10 text-center text-sm text-gray-500">
                            Нет неоплаченных начислений
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {unpaidInvoices.map(
                                (invoice: DashboardUnpaidInvoice) => (
                                    <li
                                        key={invoice.id}
                                        className="flex items-center justify-between gap-4 px-5 py-4"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {invoice.name}
                                            </p>
                                            <p className="mt-0.5 text-xs text-gray-500">
                                                Оплатить до {invoice.due_date}
                                            </p>
                                        </div>
                                        <div className="flex flex-col items-end gap-1">
                                            <span className="font-semibold">
                                                {formatMoney(invoice.remaining)}
                                            </span>
                                            <span
                                                className={cn(
                                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                                    invoiceBadge[
                                                        invoice.current_status
                                                    ]?.className,
                                                )}
                                            >
                                                {
                                                    invoiceBadge[
                                                        invoice.current_status
                                                    ]?.label
                                                }
                                            </span>
                                        </div>
                                    </li>
                                ),
                            )}
                        </ul>
                    )}

                    {unpaidInvoices.length > 0 && (
                        <div className="border-t border-gray-200 px-5 py-3">
                            <Link
                                href={payments.get()}
                                className="text-sm font-medium text-primary hover:underline"
                            >
                                История платежей
                            </Link>
                        </div>
                    )}
                </section>

                <section className="rounded-xl border border-gray-200 bg-white">
                    <div className="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <div className="flex items-center gap-2">
                            <Megaphone className="size-5 text-gray-500" />
                            <h2 className="font-semibold">Объявления</h2>
                        </div>
                        <Link
                            href={news.get()}
                            className="flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                        >
                            Все
                            <ArrowRight className="size-4" />
                        </Link>
                    </div>

                    {newsItems.length === 0 ? (
                        <div className="px-5 py-10 text-center text-sm text-gray-500">
                            Объявлений пока нет
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {newsItems.map((item) => (
                                <li key={item.id} className="px-5 py-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <p className="font-medium">
                                            {item.title}
                                        </p>
                                        <span className="shrink-0 text-xs text-gray-400">
                                            {formatDate(item.date)}
                                        </span>
                                    </div>
                                    <p className="mt-1 line-clamp-2 text-sm text-gray-600">
                                        {item.text}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
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
