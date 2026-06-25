import { Link } from '@inertiajs/react';
import {
    Calendar,
    CheckCircle2,
    CreditCard,
    Droplets,
    Home,
    Zap,
} from 'lucide-react';
import { useState } from 'react';
import PaymentFormDialog from '@/components/payments/payment-form-dialog';
import RenterMeterReadingFormDialog from '@/components/meter-readings/renter-meter-reading-form-dialog';
import ViewNewsModal from '@/components/news/view-news-modal';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';
import news from '@/routes/news';
import renterRoutes from '@/routes/renter';
import type {
    Auth,
    ChargeDisplayStatus,
    DashboardNewsItem,
    RenterChargeItem,
    RenterDashboard,
} from '@/types';
import { chargeDisplayStatusLabels, paymentStatusLabels, roomNumberLabel } from '@/types';
import type { PaymentStatus } from '@/types';

type RenterDashboardViewProps = {
    auth: Auth;
    data: RenterDashboard;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

const chargeStatusBadge: Record<ChargeDisplayStatus, string> = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    unpaid: 'bg-slate-100 text-slate-800',
    debt: 'bg-red-100 text-red-800',
};

const formatOptionalNumber = (value: number | null, digits = 3) =>
    value === null ? '—' : value.toFixed(digits);

function formatRenterName(auth: Auth): string {
    const { user } = auth;
    const fullName = [user.last_name, user.name, user.middle_name]
        .filter(Boolean)
        .join(' ');

    return fullName || user.name;
}

const paymentStatusBadge: Record<PaymentStatus, string> = {
    pending: 'text-amber-700',
    approved: 'text-green-700',
    rejected: 'text-red-700',
};

const newsAccent = ['bg-blue-500', 'bg-green-500', 'bg-orange-500', 'bg-violet-500'];

export default function RenterDashboardView({
    auth,
    data,
}: RenterDashboardViewProps) {
    const [paymentOpen, setPaymentOpen] = useState(false);
    const [selectedCharge, setSelectedCharge] =
        useState<RenterChargeItem | null>(null);
    const [meterReadingOpen, setMeterReadingOpen] = useState(false);
    const [selectedNews, setSelectedNews] = useState<DashboardNewsItem | null>(
        null,
    );
    const [newsOpen, setNewsOpen] = useState(false);

    const openPayment = (charge: RenterChargeItem | null) => {
        if (!charge) {
            return;
        }

        setSelectedCharge(charge);
        setPaymentOpen(true);
    };

    const openNews = (item: DashboardNewsItem) => {
        setSelectedNews(item);
        setNewsOpen(true);
    };

    const { summary, monthly_charges, payment_history, meter_readings } = data;

    return (
        <>
            <div className="flex flex-col gap-3 pb-4">
                <div>
                    <h1 className="text-lg font-semibold sm:text-xl">
                        Добро пожаловать, {formatRenterName(auth)}!
                    </h1>
                    <p className="text-muted-foreground text-xs sm:text-sm">
                        {summary.room
                            ? summary.room.floor !== null
                                ? `${roomNumberLabel(summary.room)}, ${summary.room.floor} этаж`
                                : roomNumberLabel(summary.room)
                            : 'Личный кабинет арендатора'}
                    </p>
                </div>

                <div className="grid grid-cols-2 gap-2 lg:grid-cols-5">
                    <Card className="gap-0 py-0 shadow-sm">
                        <CardHeader className="px-3 py-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground">
                                К оплате на сегодня
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 px-3 pb-3 pt-0">
                            <p className="text-xl font-bold text-red-600">
                                {formatAmount(summary.due_amount)}
                            </p>
                            <Button
                                size="sm"
                                className="h-8 w-full"
                                disabled={!summary.pay_charge}
                                onClick={() => openPayment(summary.pay_charge)}
                            >
                                Оплатить
                            </Button>
                        </CardContent>
                    </Card>

                    <Card className="gap-0 py-0 shadow-sm">
                        <CardHeader className="px-3 py-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground">
                                Задолженность
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-3 pb-3 pt-0">
                            <p
                                className={cn(
                                    'text-xl font-bold',
                                    summary.debt_amount > 0
                                        ? 'text-orange-600'
                                        : 'text-green-600',
                                )}
                            >
                                {formatAmount(summary.debt_amount)}
                            </p>
                            <CardDescription className="mt-1 text-xs">
                                {summary.debt_amount > 0
                                    ? 'Есть просроченный долг'
                                    : 'Отсутствует'}
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card className="gap-0 py-0 shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 px-3 py-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground">
                                Последняя оплата
                            </CardTitle>
                            <CheckCircle2 className="size-4 text-green-600" />
                        </CardHeader>
                        <CardContent className="px-3 pb-3 pt-0">
                            <p className="text-sm font-semibold">
                                {summary.last_payment?.date ?? '—'}
                            </p>
                            <CardDescription className="text-xs">
                                {summary.last_payment
                                    ? formatAmount(summary.last_payment.amount)
                                    : 'Платежей пока нет'}
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card className="gap-0 py-0 shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 px-3 py-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground">
                                Следующее начисление
                            </CardTitle>
                            <Calendar className="size-4 text-blue-600" />
                        </CardHeader>
                        <CardContent className="px-3 pb-3 pt-0">
                            <p className="text-sm font-semibold">
                                {summary.next_charge?.date ?? '—'}
                            </p>
                            <CardDescription className="text-xs">
                                {!summary.has_contract
                                    ? 'Договор не найден'
                                    : summary.next_charge
                                      ? `через ${summary.next_charge.days_until} дн.`
                                      : 'Следующих начислений нет'}
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card className="col-span-2 gap-0 py-0 shadow-sm lg:col-span-1">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 px-3 py-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground">
                                Статус комнаты
                            </CardTitle>
                            <Home className="size-4 text-blue-600" />
                        </CardHeader>
                        <CardContent className="px-3 pb-3 pt-0">
                            <p className="text-sm font-semibold text-green-700">
                                {summary.room_status}
                            </p>
                            <CardDescription className="text-xs">
                                {summary.room_status_hint}
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-3 xl:grid-cols-12 xl:items-start">
                    <div className="flex flex-col gap-3 xl:col-span-7">
                        <Card className="gap-0 py-0 shadow-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-4 py-3">
                                <CardTitle className="text-sm">
                                    Начисления за {monthly_charges.month_label}
                                </CardTitle>
                                {monthly_charges.pay_charge && (
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        className="h-8"
                                        onClick={() =>
                                            openPayment(
                                                monthly_charges.pay_charge,
                                            )
                                        }
                                    >
                                        <CreditCard className="size-4" />
                                        Оплатить
                                    </Button>
                                )}
                            </CardHeader>
                            <CardContent className="px-0 pb-0 pt-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="border-y bg-muted/40">
                                            <tr>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Услуга
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Период
                                                </th>
                                                <th className="px-4 py-2 text-right font-medium">
                                                    Сумма
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Статус
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {monthly_charges.charges.length ===
                                            0 ? (
                                                <tr>
                                                    <td
                                                        colSpan={4}
                                                        className="text-muted-foreground px-4 py-8 text-center"
                                                    >
                                                        Начислений за этот
                                                        месяц пока нет
                                                    </td>
                                                </tr>
                                            ) : (
                                                monthly_charges.charges.map(
                                                    (charge) => (
                                                        <tr
                                                            key={charge.id}
                                                            className="border-b"
                                                        >
                                                            <td className="px-4 py-2">
                                                                {charge.label}
                                                            </td>
                                                            <td className="text-muted-foreground px-4 py-2">
                                                                {charge.period}
                                                            </td>
                                                            <td className="px-4 py-2 text-right font-medium">
                                                                {formatAmount(
                                                                    charge.total_amount,
                                                                )}
                                                            </td>
                                                            <td className="px-4 py-2">
                                                                <span
                                                                    className={cn(
                                                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                                        chargeStatusBadge[
                                                                            charge.display_status
                                                                        ],
                                                                    )}
                                                                >
                                                                    {
                                                                        chargeDisplayStatusLabels[
                                                                            charge
                                                                                .display_status
                                                                        ]
                                                                    }
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )
                                            )}
                                        </tbody>
                                        {monthly_charges.charges.length > 0 && (
                                            <tfoot>
                                                <tr className="bg-muted/20 font-semibold">
                                                    <td
                                                        colSpan={3}
                                                        className="px-4 py-3"
                                                    >
                                                        Итого к оплате
                                                    </td>
                                                    <td className="px-4 py-3 text-right text-red-600">
                                                        {formatAmount(
                                                            monthly_charges.total_to_pay,
                                                        )}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        )}
                                    </table>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="gap-0 py-0 shadow-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-4 py-3">
                                <CardTitle className="text-sm">
                                    История платежей
                                </CardTitle>
                                <Button
                                    variant="link"
                                    size="sm"
                                    className="h-auto px-0 text-xs"
                                    asChild
                                >
                                    <Link href={renterRoutes.payments.get()}>
                                        Все платежи
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="px-0 pb-0 pt-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="border-y bg-muted/40">
                                            <tr>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Дата
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Услуга
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Сумма
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Статус
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {payment_history.length === 0 ? (
                                                <tr>
                                                    <td
                                                        colSpan={4}
                                                        className="text-muted-foreground px-4 py-8 text-center"
                                                    >
                                                        Платежей пока нет
                                                    </td>
                                                </tr>
                                            ) : (
                                                payment_history.map(
                                                    (payment) => (
                                                        <tr
                                                            key={payment.id}
                                                            className="border-b"
                                                        >
                                                            <td className="px-4 py-2">
                                                                {payment.date}
                                                            </td>
                                                            <td className="px-4 py-2">
                                                                {
                                                                    payment.service
                                                                }
                                                            </td>
                                                            <td className="px-4 py-2 font-medium">
                                                                {formatAmount(
                                                                    payment.amount,
                                                                )}
                                                            </td>
                                                            <td
                                                                className={cn(
                                                                    'px-4 py-2 text-xs font-medium',
                                                                    paymentStatusBadge[
                                                                        payment.status as PaymentStatus
                                                                    ],
                                                                )}
                                                            >
                                                                {paymentStatusLabels[
                                                                    payment.status as PaymentStatus
                                                                ] ??
                                                                    payment.status_label}
                                                            </td>
                                                        </tr>
                                                    ),
                                                )
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-3 xl:col-span-5">
                        <Card className="gap-0 py-0 shadow-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-4 py-3">
                                <CardTitle className="text-sm">
                                    Показания счётчиков
                                </CardTitle>
                                <Button
                                    variant="link"
                                    size="sm"
                                    className="h-auto px-0 text-xs"
                                    asChild
                                >
                                    <Link
                                        href={renterRoutes.meterReadings.get()}
                                    >
                                        Все показания
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="space-y-3 px-4 pb-4 pt-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="border-b">
                                            <tr>
                                                <th className="py-2 text-left font-medium">
                                                    Счётчик
                                                </th>
                                                <th className="py-2 text-left font-medium">
                                                    Предыд.
                                                </th>
                                                <th className="py-2 text-left font-medium">
                                                    Текущ.
                                                </th>
                                                <th className="py-2 text-left font-medium">
                                                    Расход
                                                </th>
                                                <th className="py-2 text-left font-medium">
                                                    Дата
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {meter_readings.map((reading) => (
                                                <tr
                                                    key={reading.type}
                                                    className="border-b"
                                                >
                                                    <td className="py-2">
                                                        <div className="flex items-center gap-1.5">
                                                            {reading.type ===
                                                            'electricity' ? (
                                                                <Zap className="size-3.5 text-amber-500" />
                                                            ) : (
                                                                <Droplets className="size-3.5 text-blue-500" />
                                                            )}
                                                            {reading.label}
                                                        </div>
                                                    </td>
                                                    <td className="py-2">
                                                        {formatOptionalNumber(
                                                            reading.previous_value,
                                                        )}
                                                    </td>
                                                    <td className="py-2">
                                                        {formatOptionalNumber(
                                                            reading.current_value,
                                                        )}
                                                    </td>
                                                    <td className="py-2">
                                                        {formatOptionalNumber(
                                                            reading.consumption,
                                                        )}
                                                    </td>
                                                    <td className="text-muted-foreground py-2">
                                                        {reading.reading_date ??
                                                            '—'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="rounded-lg border border-blue-100 bg-blue-50/60 p-3">
                                    <p className="mb-2 text-sm font-medium">
                                        Передайте новые показания
                                    </p>
                                    <p className="text-muted-foreground mb-3 text-xs">
                                        Показания принимаются до 25 числа
                                        текущего месяца
                                    </p>
                                    <Button
                                        size="sm"
                                        onClick={() =>
                                            setMeterReadingOpen(true)
                                        }
                                    >
                                        Передать показания
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="gap-0 py-0 shadow-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 px-4 py-3">
                                <CardTitle className="text-sm">
                                    Объявления
                                </CardTitle>
                                <Button
                                    variant="link"
                                    size="sm"
                                    className="h-auto px-0 text-xs"
                                    asChild
                                >
                                    <Link href={news.get()}>Все объявления</Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="space-y-2 px-4 pb-4 pt-0">
                                {data.news.length === 0 ? (
                                    <p className="text-muted-foreground text-sm">
                                        Объявлений пока нет
                                    </p>
                                ) : (
                                    data.news.map((item, index) => (
                                        <button
                                            key={item.id}
                                            type="button"
                                            onClick={() => openNews(item)}
                                            className="hover:bg-muted/50 w-full rounded-md border px-3 py-2 text-left transition-colors"
                                        >
                                            <div className="flex items-start gap-2">
                                                <span
                                                    className={cn(
                                                        'mt-1.5 size-2 shrink-0 rounded-full',
                                                        newsAccent[
                                                            index %
                                                                newsAccent.length
                                                        ],
                                                    )}
                                                />
                                                <div className="min-w-0">
                                                    <p className="text-sm font-medium">
                                                        {item.title}
                                                    </p>
                                                    <p className="text-muted-foreground mt-0.5 line-clamp-2 text-xs">
                                                        {item.text}
                                                    </p>
                                                    <p className="text-muted-foreground mt-1 text-[10px]">
                                                        {new Date(
                                                            item.date,
                                                        ).toLocaleDateString(
                                                            'ru-RU',
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        </button>
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        {data.useful_links.length > 0 && (
                            <Card className="gap-0 py-0 shadow-sm">
                                <CardHeader className="px-4 py-3">
                                    <CardTitle className="text-sm">
                                        Полезные ссылки
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-2 px-4 pb-4 pt-0 sm:grid-cols-2">
                                    {data.useful_links.map((link) => (
                                        <div
                                            key={link.url}
                                            className="flex flex-col justify-between rounded-md border p-3"
                                        >
                                            <div>
                                                <p className="text-sm font-medium">
                                                    {link.title}
                                                </p>
                                                <p className="text-muted-foreground mt-1 text-xs">
                                                    {link.description}
                                                </p>
                                            </div>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="mt-3"
                                                asChild
                                            >
                                                <a
                                                    href={link.url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                >
                                                    Перейти
                                                </a>
                                            </Button>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            <PaymentFormDialog
                charge={selectedCharge}
                open={paymentOpen}
                onOpenChange={setPaymentOpen}
            />

            <RenterMeterReadingFormDialog
                open={meterReadingOpen}
                onOpenChange={setMeterReadingOpen}
            />

            <ViewNewsModal
                news={selectedNews}
                open={newsOpen}
                onOpenChange={setNewsOpen}
            />
        </>
    );
}
