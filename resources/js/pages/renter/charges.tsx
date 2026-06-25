import { Form, Head, Link, usePage } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
import { useState } from 'react';
import PaymentFormDialog from '@/components/payments/payment-form-dialog';
import UtilitiesChargeBreakdown from '@/components/charges/utilities-charge-breakdown';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import renterRoutes from '@/routes/renter';
import type { ChargeDateFilters, ChargeDisplayStatus, RenterChargeItem } from '@/types';
import { chargeCategoryLabels, chargeDisplayStatusLabels } from '@/types';

type PageProps = {
    charges: RenterChargeItem[];
    filters: ChargeDateFilters;
};

const statusBadge: Record<ChargeDisplayStatus, string> = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    unpaid: 'bg-slate-100 text-slate-800',
    debt: 'bg-red-100 text-red-800',
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '—';

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function RenterChargesPage() {
    const { charges, filters } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [selectedCharge, setSelectedCharge] =
        useState<RenterChargeItem | null>(null);

    const openPayment = (charge: RenterChargeItem) => {
        setSelectedCharge(charge);
        setFormOpen(true);
    };

    return (
        <>
            <Head title="Начисления" />

            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-semibold">Начисления</h1>
                <p className="text-gray-500">
                    Список ваших начислений и оплат
                </p>
            </div>

            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-4">
                <Form
                    method="get"
                    action={renterRoutes.charges.url()}
                    className="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end"
                >
                    <div className="grid gap-2">
                        <label
                            htmlFor="created_from"
                            className="text-sm font-medium text-gray-600"
                        >
                            Дата создания от
                        </label>
                        <input
                            id="created_from"
                            name="created_from"
                            type="date"
                            defaultValue={filters.created_from ?? ''}
                            className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        />
                    </div>
                    <div className="grid gap-2">
                        <label
                            htmlFor="created_to"
                            className="text-sm font-medium text-gray-600"
                        >
                            Дата создания до
                        </label>
                        <input
                            id="created_to"
                            name="created_to"
                            type="date"
                            defaultValue={filters.created_to ?? ''}
                            className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        />
                    </div>
                    <div className="flex gap-2">
                        <button
                            type="submit"
                            className="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Применить
                        </button>
                        <Link
                            href={renterRoutes.charges()}
                            className="inline-flex h-9 items-center justify-center rounded-md border border-input bg-transparent px-4 text-sm font-medium hover:bg-accent"
                        >
                            Сбросить
                        </Link>
                    </div>
                </Form>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Список начислений</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Дата создания
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Тип
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Полная сумма
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Оплачено
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Остаток
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Крайняя дата
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
                            {charges.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Начислений не найдено
                                    </td>
                                </tr>
                            ) : (
                                charges.map((charge) => (
                                    <tr
                                        key={charge.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {formatDate(charge.created_at)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {
                                                chargeCategoryLabels[
                                                    charge.category
                                                ]
                                            }
                                        </td>
                                        <td className="px-6 py-4">
                                            {charge.category === 'utilities' &&
                                            charge.breakdown &&
                                            charge.breakdown.length > 0 ? (
                                                <UtilitiesChargeBreakdown
                                                    breakdown={
                                                        charge.breakdown
                                                    }
                                                    totalAmount={
                                                        charge.total_amount
                                                    }
                                                />
                                            ) : (
                                                formatAmount(
                                                    charge.total_amount,
                                                )
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatAmount(charge.paid_amount)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatAmount(
                                                charge.remaining_amount,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatDate(
                                                charge.last_payment_date,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={cn(
                                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                    statusBadge[
                                                        charge.display_status
                                                    ],
                                                )}
                                            >
                                                {
                                                    chargeDisplayStatusLabels[
                                                        charge.display_status
                                                    ]
                                                }
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end">
                                                {charge.can_pay ? (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            openPayment(charge)
                                                        }
                                                    >
                                                        <CreditCard
                                                            size={16}
                                                        />
                                                        Оплатить
                                                    </Button>
                                                ) : (
                                                    <span className="text-sm text-gray-400">
                                                        —
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <PaymentFormDialog
                charge={selectedCharge}
                open={formOpen}
                onOpenChange={setFormOpen}
            />
        </>
    );
}

RenterChargesPage.layout = {
    breadcrumbs: [
        {
            title: 'Начисления',
            href: renterRoutes.charges(),
        },
    ],
};
