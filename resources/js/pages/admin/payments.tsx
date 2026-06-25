import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, Eye, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import payments from '@/routes/payments';
import renters from '@/routes/renters';
import type { PaymentListItem, PaymentStatus } from '@/types';
import { paymentStatusLabels } from '@/types';

type PageProps = {
    payments: PaymentListItem[];
};

const statusBadge: Record<PaymentStatus, string> = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function PaymentsPage() {
    const { payments: paymentsList } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Платежи" />

            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-semibold">Платежи</h1>
                <p className="text-gray-500">
                    Подтверждение платежей арендаторов
                </p>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Список платежей</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Дата
                                </th>
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
                            {paymentsList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Платежей пока нет
                                    </td>
                                </tr>
                            ) : (
                                paymentsList.map((payment) => (
                                    <tr
                                        key={payment.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {payment.created_at}
                                        </td>
                                        <td className="px-6 py-4">
                                            <Link
                                                href={renters.settings(
                                                    payment.renter.id,
                                                )}
                                                className="font-medium hover:underline"
                                            >
                                                {payment.renter.full_name}
                                            </Link>
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatAmount(
                                                payment.charge.total_amount,
                                            )}
                                            <span className="block text-xs text-gray-500">
                                                от {payment.charge.created_at}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 font-medium">
                                            {formatAmount(payment.amount)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <a
                                                href={payment.receipt_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="inline-flex items-center gap-1 text-sm text-primary hover:underline"
                                            >
                                                <Eye size={16} />
                                                Открыть
                                            </a>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={cn(
                                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                    statusBadge[payment.status],
                                                )}
                                            >
                                                {
                                                    paymentStatusLabels[
                                                        payment.status
                                                    ]
                                                }
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            {payment.status === 'pending' ? (
                                                <div className="flex items-center justify-end gap-2">
                                                    <Form
                                                        {...payments.approve.form(
                                                            payment.id,
                                                        )}
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                    >
                                                        {({ processing }) => (
                                                            <Button
                                                                type="submit"
                                                                size="sm"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                <Check
                                                                    size={16}
                                                                />
                                                                Подтвердить
                                                            </Button>
                                                        )}
                                                    </Form>
                                                    <Form
                                                        {...payments.reject.form(
                                                            payment.id,
                                                        )}
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                    >
                                                        {({ processing }) => (
                                                            <Button
                                                                type="submit"
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                <X size={16} />
                                                                Отклонить
                                                            </Button>
                                                        )}
                                                    </Form>
                                                </div>
                                            ) : (
                                                <span className="block text-right text-sm text-gray-400">
                                                    —
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

PaymentsPage.layout = {
    breadcrumbs: [
        {
            title: 'Платежи',
            href: payments.get(),
        },
    ],
};
