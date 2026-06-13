import { Head, Link, router, usePage } from '@inertiajs/react';
import { Edit, Plus, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import AdminInvoicesController from '@/actions/App/Http/Controllers/Admin/Invoices/AdminInvoicesController';
import AdminInvoiceFormDialog from '@/components/invoices/admin-invoice-form-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import type { InvoiceRenter, Invoices, InvoiceStatus } from '@/types';

type InvoiceFilters = {
    from: string;
    to: string;
};

type PageProps = {
    invoices: Invoices[];
    renters: InvoiceRenter[];
    filters: InvoiceFilters;
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

const renterName = (renter?: InvoiceRenter | null) =>
    renter
        ? [renter.last_name, renter.name, renter.middle_name]
              .filter(Boolean)
              .join(' ')
        : '—';

const formatDate = (value: string) => {
    if (!value) {
        return '—';
    }

    if (/^\d{2}\.\d{2}\.\d{4}$/.test(value)) {
        return value;
    }

    return new Date(value).toLocaleDateString('ru-RU');
};

export default function AdminInvoicesPage() {
    const { invoices, renters, filters } = usePage<PageProps>().props;
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<Invoices | null>(null);
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

            if (from) {
                query.from = from;
            }

            if (to) {
                query.to = to;
            }

            router.get(AdminInvoicesController.getInvoices().url, query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [from, to]);

    const resetFilters = () => {
        setFrom('');
        setTo('');
    };

    const openCreate = () => {
        setEditing(null);
        setDialogOpen(true);
    };

    const openEdit = (invoice: Invoices) => {
        setEditing(invoice);
        setDialogOpen(true);
    };

    return (
        <>
            <Head title="Начисления" />

            <div className="flex items-center justify-between">
                <PageHeader
                    title="Начисления"
                    description="Управление начислениями арендаторов"
                />
                <Button onClick={openCreate} className="gap-2">
                    <Plus size={16} />
                    Создать
                </Button>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-end sm:justify-between">
                    <h3 className="font-medium">
                        Список начислений ({invoices.length})
                    </h3>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="grid gap-1">
                            <label
                                htmlFor="from"
                                className="text-xs font-medium text-gray-500"
                            >
                                С месяца
                            </label>
                            <Input
                                id="from"
                                type="month"
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
                                По месяц
                            </label>
                            <Input
                                id="to"
                                type="month"
                                value={to}
                                min={from || undefined}
                                onChange={(event) => setTo(event.target.value)}
                                className="sm:w-44"
                            />
                        </div>
                        {(from || to) && (
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
                                    Название
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Сумма
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Оплачено
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Срок
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
                            {invoices.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        {from || to
                                            ? 'Начисления за выбранный период не найдены'
                                            : 'Нет начислений'}
                                    </td>
                                </tr>
                            ) : (
                                invoices.map((invoice) => {
                                    const badge =
                                        statusBadge[invoice.current_status];

                                    return (
                                        <tr
                                            key={invoice.id}
                                            className="border-b border-gray-100 hover:bg-gray-50"
                                        >
                                            <td className="px-6 py-4 font-medium">
                                                {renterName(invoice.user)}
                                            </td>
                                            <td className="px-6 py-4">
                                                {invoice.name}
                                            </td>
                                            <td className="px-6 py-4">
                                                {invoice.total_price} ₽
                                            </td>
                                            <td className="px-6 py-4">
                                                {invoice.paid_price} ₽
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {formatDate(invoice.due_date)}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${badge.className}`}
                                                >
                                                    {badge.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="gap-2"
                                                        onClick={() =>
                                                            openEdit(invoice)
                                                        }
                                                    >
                                                        <Edit size={16} />
                                                        Изменить
                                                    </Button>
                                                    <Link
                                                        href={AdminInvoicesController.deleteInvoice(
                                                            invoice.id,
                                                        )}
                                                        as="button"
                                                        method="delete"
                                                        className="rounded-lg bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
                                                        preserveScroll
                                                    >
                                                        <Trash2 size={16} />
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <AdminInvoiceFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                renters={renters}
                invoice={editing}
            />
        </>
    );
}
