import { Head, Link, usePage } from '@inertiajs/react';
import { Edit, Plus } from 'lucide-react';
import { useState } from 'react';
import ChargeFormDialog from '@/components/charges/charge-form-dialog';
import UtilitiesChargeBreakdown from '@/components/charges/utilities-charge-breakdown';
import { Button } from '@/components/ui/button';
import { useOpenCreateFromQuery } from '@/hooks/use-open-create-from-query';
import { cn } from '@/lib/utils';
import charges from '@/routes/charges';
import renters from '@/routes/renters';
import type { Charge, ChargeDisplayStatus, ChargeRenterOption } from '@/types';
import { chargeCategoryLabels, chargeDisplayStatusLabels, roomWithFloorLabel } from '@/types';

type PageProps = {
    charges: Charge[];
    renters: ChargeRenterOption[];
    showArchive: boolean;
};

const statusBadge: Record<ChargeDisplayStatus, string> = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    unpaid: 'bg-slate-100 text-slate-800',
    debt: 'bg-red-100 text-red-800',
    archived: 'bg-gray-100 text-gray-600',
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '—';

const formatRoom = (charge: Charge): string => {
    if (charge.renter.room_label) {
        return charge.renter.room_label;
    }

    if (!charge.renter.room) {
        return '—';
    }

    return roomWithFloorLabel(charge.renter.room);
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function ChargesPage() {
    const {
        charges: chargesList,
        renters: rentersList,
        showArchive,
    } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [selectedCharge, setSelectedCharge] = useState<Charge | null>(null);

    const openCreate = () => {
        setSelectedCharge(null);
        setFormOpen(true);
    };

    useOpenCreateFromQuery(openCreate);

    const openEdit = (charge: Charge) => {
        setSelectedCharge(charge);
        setFormOpen(true);
    };

    return (
        <>
            <Head title="Начисления" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">
                        Начисления
                    </h1>
                    <p className="text-gray-500">
                        Учёт начислений и оплат арендаторов
                    </p>
                </div>
                {!showArchive && (
                    <Button
                        onClick={openCreate}
                        disabled={rentersList.length === 0}
                    >
                        <Plus size={20} />
                        Добавить начисление
                    </Button>
                )}
            </div>

            <div className="mb-4 flex gap-2">
                <Link
                    href={charges.get()}
                    className={cn(
                        'rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                        !showArchive
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    )}
                >
                    Активные
                </Link>
                <Link
                    href={charges.get({ query: { archive: '1' } })}
                    className={cn(
                        'rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                        showArchive
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    )}
                >
                    Архив
                </Link>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">
                        {showArchive
                            ? 'Архивные начисления'
                            : 'Список начислений'}
                    </h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Арендатор
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Комната
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
                                    Крайняя дата
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Статус
                                </th>
                                {!showArchive && (
                                    <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                        Действия
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {chargesList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={showArchive ? 7 : 8}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        {showArchive
                                            ? 'Архивных начислений нет'
                                            : 'Начислений пока нет'}
                                    </td>
                                </tr>
                            ) : (
                                chargesList.map((charge) => (
                                    <tr
                                        key={charge.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {charge.renter.id !== null ? (
                                                <Link
                                                    href={renters.settings(
                                                        charge.renter.id,
                                                    )}
                                                    className="font-medium hover:underline"
                                                >
                                                    {charge.renter.full_name}
                                                </Link>
                                            ) : (
                                                <span className="font-medium">
                                                    {charge.renter.full_name}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatRoom(charge)}
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
                                        {!showArchive && (
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            openEdit(charge)
                                                        }
                                                    >
                                                        <Edit size={16} />
                                                        Изменить
                                                    </Button>
                                                </div>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {!showArchive && (
                <ChargeFormDialog
                    charge={selectedCharge}
                    renters={rentersList}
                    open={formOpen}
                    onOpenChange={setFormOpen}
                />
            )}
        </>
    );
}

ChargesPage.layout = {
    breadcrumbs: [
        {
            title: 'Начисления',
            href: charges.get(),
        },
    ],
};
