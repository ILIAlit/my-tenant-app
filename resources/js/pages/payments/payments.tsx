import { usePage } from '@inertiajs/react';
import ReceiptViewer from '@/components/payments/receipt-viewer';
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/ui/page-header';
import type { Payment, PaymentStatus } from '@/types';

type PageProps = {
    payments: Payment[];
};

const statusConfig: Record<
    PaymentStatus,
    { label: string; variant: 'default' | 'secondary' | 'destructive' }
> = {
    review: { label: 'На проверке', variant: 'secondary' },
    approved: { label: 'Подтверждён', variant: 'default' },
    rejected: { label: 'Отклонён', variant: 'destructive' },
};

const formatDate = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export default function PaymentsPage() {
    const { payments } = usePage<PageProps>().props;

    return (
        <>
            <PageHeader
                title="Платежи"
                description="История ваших платежей по начислениям"
            />

            {payments.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    У вас пока нет платежей
                </div>
            ) : (
                <div className="space-y-4">
                    {payments.map((payment) => {
                        const status = statusConfig[payment.status];

                        return (
                            <div
                                key={payment.id}
                                className="max-w-lg rounded-xl border border-gray-200 bg-white"
                            >
                                <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                                    <h3 className="font-medium">
                                        {payment.invoice?.name ??
                                            'Начисление удалено'}
                                    </h3>
                                    <Badge variant={status.variant}>
                                        {status.label}
                                    </Badge>
                                </div>
                                <div className="space-y-3 p-6">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">
                                            Сумма
                                        </span>
                                        <span className="text-lg font-semibold">
                                            {payment.amount} ₽
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">
                                            Дата
                                        </span>
                                        <span className="text-sm">
                                            {formatDate(payment.created_at)}
                                        </span>
                                    </div>
                                    {payment.receipt_url && (
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-gray-600">
                                                Чек
                                            </span>
                                            <ReceiptViewer
                                                url={payment.receipt_url}
                                                title={`Чек — ${payment.invoice?.name ?? 'платёж'}`}
                                            />
                                        </div>
                                    )}
                                    {payment.status === 'rejected' &&
                                        payment.rejection_reason && (
                                            <div className="rounded-lg border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">
                                                {payment.rejection_reason}
                                            </div>
                                        )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );
}
