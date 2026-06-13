import { Head, Link, router, usePage } from '@inertiajs/react';
import { Check, Edit, Settings2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import AdminUtilityReadingsController from '@/actions/App/Http/Controllers/Admin/UtilityReadings/AdminUtilityReadingsController';
import AdminUtilityReadingFormDialog from '@/components/utility-readings/admin-utility-reading-form-dialog';
import RejectUtilityReadingDialog from '@/components/utility-readings/reject-utility-reading-dialog';
import UtilityTariffsForm from '@/components/utility-readings/utility-tariffs-form';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import utilityReadings from '@/routes/utility-readings';
import type {
    InvoiceRenter,
    UtilityReading,
    UtilityReadingStatus,
    UtilityTariff,
} from '@/types';

type ReadingFilters = {
    status: string;
};

type PageProps = {
    readings: UtilityReading[];
    tariffs: UtilityTariff;
    filters: ReadingFilters;
};

const statusOptions = [
    { value: '', label: 'Все статусы' },
    { value: 'review', label: 'На проверке' },
    { value: 'approved', label: 'Одобрено' },
    { value: 'rejected', label: 'Не одобрено' },
];

const statusBadge: Record<
    UtilityReadingStatus,
    { className: string; label: string }
> = {
    review: {
        className: 'bg-yellow-100 text-yellow-800',
        label: 'На проверке',
    },
    approved: {
        className: 'bg-green-100 text-green-800',
        label: 'Одобрено',
    },
    rejected: {
        className: 'bg-red-100 text-red-800',
        label: 'Не одобрено',
    },
};

const renterName = (renter?: InvoiceRenter | null) =>
    renter
        ? [renter.last_name, renter.name, renter.middle_name]
              .filter(Boolean)
              .join(' ')
        : '—';

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

const formatReading = (value: string | null) => (value !== null ? value : '—');

const formatAmount = (value: number) =>
    new Intl.NumberFormat('ru-RU').format(value);

export default function AdminUtilityReadingsPage() {
    const { readings, tariffs, filters } = usePage<PageProps>().props;
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<UtilityReading | null>(null);
    const [rejecting, setRejecting] = useState<UtilityReading | null>(null);
    const [status, setStatus] = useState(filters?.status ?? '');
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

            router.get(
                AdminUtilityReadingsController.getAllReadings().url,
                query,
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [status]);

    const openEdit = (reading: UtilityReading) => {
        setEditing(reading);
        setDialogOpen(true);
    };

    return (
        <>
            <Head title="Показания счётчиков" />

            <PageHeader
                title="Показания счётчиков"
                description="Проверка и подтверждение показаний коммунальных услуг"
            />

            <div className="mb-6">
                <UtilityTariffsForm tariffs={tariffs} />
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <h3 className="font-medium">
                        Список показаний ({readings.length})
                    </h3>
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
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Период
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Комната
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Арендатор
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Холодная вода
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Горячая вода
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Электроэнергия
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Фото
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Статус
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Начислено
                                </th>
                                <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {readings.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={10}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        {status
                                            ? 'Показания не найдены'
                                            : 'Показаний пока нет'}
                                    </td>
                                </tr>
                            ) : (
                                readings.map((reading) => {
                                    const badge = statusBadge[reading.status];
                                    const isPending =
                                        reading.status === 'review';

                                    return (
                                        <tr
                                            key={reading.id}
                                            className="border-b border-gray-100"
                                        >
                                            <td className="px-6 py-4 text-sm">
                                                {formatDate(
                                                    reading.period_start,
                                                )}{' '}
                                                —{' '}
                                                {formatDate(reading.period_end)}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {reading.room
                                                    ? `№${reading.room.number}`
                                                    : '—'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {renterName(
                                                    reading.room?.user,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {formatReading(
                                                    reading.cold_water,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {formatReading(
                                                    reading.hot_water,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {formatReading(
                                                    reading.electricity,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <div className="flex flex-wrap gap-2">
                                                    {reading.cold_water_photo_url && (
                                                        <a
                                                            href={
                                                                reading.cold_water_photo_url
                                                            }
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-primary underline"
                                                        >
                                                            ХВ
                                                        </a>
                                                    )}
                                                    {reading.hot_water_photo_url && (
                                                        <a
                                                            href={
                                                                reading.hot_water_photo_url
                                                            }
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-primary underline"
                                                        >
                                                            ГВ
                                                        </a>
                                                    )}
                                                    {reading.electricity_photo_url && (
                                                        <a
                                                            href={
                                                                reading.electricity_photo_url
                                                            }
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-primary underline"
                                                        >
                                                            Эл
                                                        </a>
                                                    )}
                                                    {!reading.cold_water_photo_url &&
                                                        !reading.hot_water_photo_url &&
                                                        !reading.electricity_photo_url &&
                                                        '—'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <span
                                                    className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${badge.className}`}
                                                >
                                                    {badge.label}
                                                </span>
                                                {reading.status === 'rejected' &&
                                                    reading.rejection_reason && (
                                                        <p className="mt-1 max-w-xs text-xs text-gray-500">
                                                            {
                                                                reading.rejection_reason
                                                            }
                                                        </p>
                                                    )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {reading.status === 'approved' &&
                                                reading.utility_amount > 0
                                                    ? `${formatAmount(reading.utility_amount)} ₽`
                                                    : '—'}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    {isPending && (
                                                        <>
                                                            <Link
                                                                href={AdminUtilityReadingsController.approve(
                                                                    reading.id,
                                                                )}
                                                                as="button"
                                                                method="put"
                                                                preserveScroll
                                                                className="flex items-center gap-1 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700 hover:bg-green-100"
                                                            >
                                                                <Check
                                                                    size={14}
                                                                />
                                                                Одобрить
                                                            </Link>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="gap-1 text-red-600"
                                                                onClick={() =>
                                                                    setRejecting(
                                                                        reading,
                                                                    )
                                                                }
                                                            >
                                                                <X size={14} />
                                                                Отклонить
                                                            </Button>
                                                        </>
                                                    )}
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="gap-1"
                                                        onClick={() =>
                                                            openEdit(reading)
                                                        }
                                                    >
                                                        <Edit size={14} />
                                                        Изменить
                                                    </Button>
                                                    {reading.room && (
                                                        <Link
                                                            href={utilityReadings.adminGet(
                                                                reading.room.id,
                                                            )}
                                                            className="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50"
                                                        >
                                                            <Settings2
                                                                size={14}
                                                            />
                                                        </Link>
                                                    )}
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

            <AdminUtilityReadingFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                reading={editing}
            />

            {rejecting && (
                <RejectUtilityReadingDialog
                    reading={rejecting}
                    open={Boolean(rejecting)}
                    onOpenChange={(open) => !open && setRejecting(null)}
                />
            )}
        </>
    );
}
