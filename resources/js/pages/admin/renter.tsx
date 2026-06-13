import { Head, Link, router, usePage } from '@inertiajs/react';
import { Edit, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import AdminRenterController from '@/actions/App/Http/Controllers/Admin/Renter/AdminRenterController';
import RenterFormDialog from '@/components/renters/renter-form-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import renters from '@/routes/renters';
import type { User } from '@/types';

type RenterPaymentStatus = 'none' | 'pending' | 'paid' | 'debt';

type Renter = User & {
    invoices_total: number | null;
    invoices_paid: number | null;
    payment_status: RenterPaymentStatus;
};

type RenterFilters = {
    search: string;
    status: string;
};

type PageProps = {
    renters: Renter[];
    filters: RenterFilters;
};

const statusOptions = [
    { value: '', label: 'Все статусы' },
    { value: 'none', label: 'Нет начислений' },
    { value: 'pending', label: 'Ожидает оплаты' },
    { value: 'paid', label: 'Оплачено' },
    { value: 'debt', label: 'Есть долг' },
];

const statusBadge: Record<
    RenterPaymentStatus,
    { className: string; label: string }
> = {
    none: { className: 'bg-gray-100 text-gray-600', label: 'Нет начислений' },
    pending: {
        className: 'bg-yellow-100 text-yellow-800',
        label: 'Ожидает оплаты',
    },
    paid: { className: 'bg-green-100 text-green-800', label: 'Оплачено' },
    debt: { className: 'bg-red-100 text-red-800', label: 'Есть долг' },
};

export default function RentersPage() {
    const { renters: items, filters } = usePage<PageProps>().props;
    const [editing, setEditing] = useState<Renter | null>(null);
    const [search, setSearch] = useState(filters?.search ?? '');
    const [status, setStatus] = useState(filters?.status ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            const query: Record<string, string> = {};

            if (search.trim()) {
                query.search = search.trim();
            }

            if (status) {
                query.status = status;
            }

            router.get(renters.get().url, query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [search, status]);

    return (
        <>
            <Head title="Арендаторы" />

            <PageHeader
                title="Арендаторы"
                description="Управление арендаторами и контактами"
            />

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <h3 className="font-medium">
                        Список арендаторов ({items.length})
                    </h3>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div className="relative">
                            <Search
                                size={16}
                                className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            />
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Поиск по ФИО"
                                className="pl-9 sm:w-64"
                            />
                        </div>
                        <select
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                            className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
                        >
                            {statusOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    ФИО
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Телефон
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Почта
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Начислено
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Оплачено
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Долг
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
                            {items.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        {search.trim() || status
                                            ? 'Арендаторы не найдены'
                                            : 'Нет арендаторов'}
                                    </td>
                                </tr>
                            ) : (
                                items.map((renter) => {
                                    const charged = renter.invoices_total ?? 0;
                                    const paid = renter.invoices_paid ?? 0;
                                    const debt = charged - paid;
                                    const status =
                                        statusBadge[renter.payment_status] ??
                                        statusBadge.none;

                                    return (
                                        <tr
                                            key={renter.id}
                                            className="border-b border-gray-100 hover:bg-gray-50"
                                        >
                                            <td className="px-6 py-4 font-medium">
                                                {renter.last_name} {renter.name}{' '}
                                                {renter.middle_name}
                                            </td>
                                            <td className="px-6 py-4">
                                                {renter.phone || '—'}
                                            </td>
                                            <td className="px-6 py-4">
                                                {renter.email}
                                            </td>
                                            <td className="px-6 py-4">
                                                {charged} ₽
                                            </td>
                                            <td className="px-6 py-4">
                                                {paid} ₽
                                            </td>
                                            <td className="px-6 py-4 font-medium">
                                                {debt > 0 ? `${debt} ₽` : '0 ₽'}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${status.className}`}
                                                >
                                                    {status.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="gap-1"
                                                        onClick={() =>
                                                            setEditing(renter)
                                                        }
                                                    >
                                                        <Edit size={16} />
                                                        Изменить
                                                    </Button>
                                                    <Link
                                                        href={AdminRenterController.deleteRenters(
                                                            renter.id,
                                                        )}
                                                        as="button"
                                                        method="delete"
                                                        preserveScroll
                                                        className="rounded-lg bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
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

            {editing && (
                <RenterFormDialog
                    renter={editing}
                    open={Boolean(editing)}
                    onOpenChange={(open) => !open && setEditing(null)}
                />
            )}
        </>
    );
}

RentersPage.layout = {
    breadcrumbs: [
        {
            title: 'Арендаторы',
            href: renters.get(),
        },
    ],
};
