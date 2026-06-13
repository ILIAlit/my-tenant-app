import { Head, Link, router, usePage } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import AdminPaymentsController from '@/actions/App/Http/Controllers/Admin/Payments/AdminPaymentsController';
import RejectPaymentDialog from '@/components/payments/reject-payment-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import type { InvoiceRenter, Payment, PaymentStatus } from '@/types';

type PaymentFilters = {
    status: string;
    from: string;
    to: string;
};

type PageProps = {
    payments: Payment[];
    filters: PaymentFilters;
};

const statusOptions = [
    { value: '', label: 'Все статусы' },
    { value: 'review', label: 'На проверке' },
    { value: 'approved', label: 'Одобрен' },
    { value: 'rejected', label: 'Отклонён' },
];

const statusBadge: Record<PaymentStatus, { className: string; label: string }> =
    {
        review: {
            className: 'bg-yellow-100 text-yellow-800',
            label: 'На проверке',
        },
        approved: {
            className: 'bg-green-100 text-green-800',
            label: 'Одобрен',
        },
        rejected: { className: 'bg-red-100 text-red-800', label: 'Отклонён' },
    };

const renterName = (renter?: InvoiceRenter | null) =>
    renter
        ? [renter.last_name, renter.name, renter.middle_name]
              .filter(Boolean)
              .join(' ')
        : '—';

const formatDate = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export default function AdminPaymentsPage() {
    const { payments, filters } = usePage<PageProps>().props;
    const [rejecting, setRejecting] = useState<Payment | null>(null);
    const [status, setStatus] = useState(filters?.status ?? '');
    const [from, setFrom] = useState(filters?.from ?? '');
    const [to, setTo] = useState(filters?.to ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            const query: Record<string, string> = {};

            if (status) {
                query.status = status;
            }

            if (from) {
                query.from = from;
            }

            if (to) {
                query.to = to;
            }

            router.get(AdminPaymentsController.getPayments().url, query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [status, from, to]);

    const resetFilters = () => {
        setStatus('');
        setFrom('');
        setTo('');
    };

    return (
        <>
            <Head title="Платежи" />

            <PageHeader
                title="Платежи"
                description="Проверка и подтверждение платежей арендаторов"
            />

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-end lg:justify-between">
                    <h3 className="font-medium">
                        Список платежей ({payments.length})
                    </h3>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="grid gap-1">
                            <label
                                htmlFor="status"
                                className="text-xs font-medium text-gray-500"
                            >
                                Статус
                            </label>
                            <select
                                id="status"
                                value={status}
                                onChange={(event) =>
                                    setStatus(event.target.value)
                                }
                                className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
                            >
                                {statusOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-1">
                            <label
                                htmlFor="from"
                                className="text-xs font-medium text-gray-500"
                            >
                                С даты
                            </label>
                            <Input
                                id="from"
                                type="date"
                                value={from}
                                max={to || undefined}
                                onChange={(event) => setFrom(event.target.value)}
                                className="sm:w-44"
                            />
                        </div>
                        <div className="grid gap-1">
                            <label
                                htmlFor="to"
                                className="text-xs font-medium text-gray-500"
                            >
                                По дату
                            </label>
                            <Input
                                id="to"
                                type="date"
                                value={to}
                                min={from || undefined}
                                onChange={(event) => setTo(event.target.value)}
                                className="sm:w-44"
                            />
                        </div>
                        {(status || from || to) && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetFilters}
                            >
                                Сбросить
                            </Button>
                        )}
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Арендатор
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Начисление
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Сумма
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Дата
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Чек
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Статус
                                </th>
                                <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {payments.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        {status || from || to
                                            ? 'Платежи не найдены'
                                            : 'Нет платежей'}
                                    </td>
                                </tr>
                            ) : (
                                payments.map((payment) => {
                                    const badge = statusBadge[payment.status];
                                    const isPending =
                                        payment.status === 'review';

                                    return (
                                        <tr
                                            key={payment.id}
                                            className="border-b border-gray-100 hover:bg-gray-50"
                                        >
                                            <td className="px-6 py-4 font-medium">
                                                {renterName(
                                                    payment.invoice?.user,
                                                )}
                                            </td>
                                            <td className="px-6 py-4">
                                                {payment.invoice?.name ?? '—'}
                                            </td>
                                            <td className="px-6 py-4">
                                                {payment.amount} ₽
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {formatDate(payment.created_at)}
                                            </td>
                                            <td className="px-6 py-4">
                                                {payment.receipt_url ? (
                                                    <a
                                                        href={
                                                            payment.receipt_url
                                                        }
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-sm font-medium text-primary underline"
                                                    >
                                                        Открыть
                                                    </a>
                                                ) : (
                                                    '—'
                                                )}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${badge.className}`}
                                                >
                                                    {badge.label}
                                                </span>
                                                {payment.status === 'rejected' &&
                                                    payment.rejection_reason && (
                                                        <p className="mt-1 max-w-xs text-xs text-gray-500">
                                                            {
                                                                payment.rejection_reason
                                                            }
                                                        </p>
                                                    )}
                                            </td>
                                            <td className="px-6 py-4">
                                                {isPending ? (
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={AdminPaymentsController.approvePayment(
                                                                payment.id,
                                                            )}
                                                            as="button"
                                                            method="put"
                                                            preserveScroll
                                                            className="flex items-center gap-1 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700 hover:bg-green-100"
                                                        >
                                                            <Check size={16} />
                                                            Одобрить
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            className="gap-1 text-red-600"
                                                            onClick={() =>
                                                                setRejecting(
                                                                    payment,
                                                                )
                                                            }
                                                        >
                                                            <X size={16} />
                                                            Отклонить
                                                        </Button>
                                                    </div>
                                                ) : (
                                                    <span className="block text-right text-sm text-gray-400">
                                                        —
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {rejecting && (
                <RejectPaymentDialog
                    payment={rejecting}
                    open={Boolean(rejecting)}
                    onOpenChange={(open) => !open && setRejecting(null)}
                />
            )}
        </>
    );
}
