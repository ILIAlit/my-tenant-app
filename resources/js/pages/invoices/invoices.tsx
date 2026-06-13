import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import PaymentDialog from '@/components/invoices/payment-dialog';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import type { Invoices, InvoiceStatus } from '@/types';

type PageProps = {
    invoices: Invoices[];
};

const statusBadge: Record<InvoiceStatus, { className: string; label: string }> =
    {
        debt: { className: 'bg-red-100 text-red-800', label: 'Долг' },
        review: {
            className: 'bg-yellow-100 text-yellow-800',
            label: 'На проверке',
        },
        paid: { className: 'bg-green-100 text-green-800', label: 'Оплачено' },
    };

export default function InvoicesPage() {
    const page = usePage<PageProps>();
    const { invoices } = page.props;
    const [selectedInvoice, setSelectedInvoice] = useState<Invoices | null>(
        null,
    );
    const [paymentDialogOpen, setPaymentDialogOpen] = useState(false);

    const handlePayClick = (invoice: Invoices) => {
        setSelectedInvoice(invoice);
        setPaymentDialogOpen(true);
    };

    return (
        <>
            <PageHeader
                title="Начисления"
                description="История начислений по вашей квартире"
            />

{invoices.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    У вас пока нет начислений
                </div>
            ) : (
            <div className="space-y-4">
                {invoices.map((invoicesItem) => {
                    const badge = statusBadge[invoicesItem.current_status];

                    return (
                        <div
                            key={invoicesItem.id}
                            className="max-w-lg rounded-xl border border-gray-200 bg-white"
                        >
                            <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                                <h3 className="font-medium">
                                    {invoicesItem.name}
                                </h3>
                                <span
                                    className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${badge.className}`}
                                >
                                    {badge.label}
                                </span>
                            </div>
                            <div className="p-6">
                                <div className="space-y-3">
                                    <div className="text-sm text-gray-600">
                                        {invoicesItem.create_date} -{' '}
                                        {invoicesItem.due_date}
                                    </div>
                                    <div className="flex items-center justify-between border-t border-gray-200 pt-3">
                                        <span className="font-semibold">
                                            Итого
                                        </span>
                                        <span className="text-lg font-semibold">
                                            {invoicesItem.total_price}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between border-t border-gray-200 pt-3">
                                        <span className="font-semibold">
                                            Оплачено
                                        </span>
                                        <span className="text-lg font-semibold">
                                            {invoicesItem.paid_price}
                                        </span>
                                    </div>
                                </div>
                                <Button
                                    onClick={() => handlePayClick(invoicesItem)}
                                    className="mt-4 w-full"
                                >
                                    Оплатить
                                </Button>
                            </div>
                        </div>
                    );
                })}
            </div>

)}
{selectedInvoice && (
    <PaymentDialog
        invoice={selectedInvoice}
        open={paymentDialogOpen}
        onOpenChange={setPaymentDialogOpen}
    />
)}

        </>
    );
}
