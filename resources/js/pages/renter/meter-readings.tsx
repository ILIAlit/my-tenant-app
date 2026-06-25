import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import RenterMeterReadingFormDialog from '@/components/meter-readings/renter-meter-reading-form-dialog';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import renterRoutes from '@/routes/renter';
import type {
    MeterReadingFilters,
    MeterReadingStatus,
    MeterType,
    RenterMeterReadingItem,
} from '@/types';
import { meterReadingStatusLabels, meterTypeLabels, meterTypeOptions } from '@/types';

type PageProps = {
    meterReadings: RenterMeterReadingItem[];
    filters: MeterReadingFilters;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });

const formatValue = (type: MeterType, value: number): string => {
    const unit = type === 'electricity' ? 'кВт·ч' : 'м³';

    return `${value.toFixed(3)} ${unit}`;
};

const formatOptionalValue = (
    type: MeterType,
    value: number | null,
): string => (value === null ? '—' : formatValue(type, value));

const formatAmount = (value: number | null): string =>
    value === null ? '—' : `${value.toFixed(2)} BYN`;

const statusBadge: Record<MeterReadingStatus, string> = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

export default function RenterMeterReadingsPage() {
    const { meterReadings, filters } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);

    return (
        <>
            <Head title="Показания счётчиков" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">
                        Показания счётчиков
                    </h1>
                    <p className="text-gray-500">
                        Передача показаний горячей и холодной воды, электричества
                    </p>
                </div>
                <Button onClick={() => setFormOpen(true)}>
                    <Plus size={20} />
                    Передать показание
                </Button>
            </div>

            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-4">
                <Form
                    method="get"
                    action={renterRoutes.meterReadings.url()}
                    className="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end"
                >
                    <div className="grid gap-2">
                        <label
                            htmlFor="type"
                            className="text-sm font-medium text-gray-600"
                        >
                            Тип счётчика
                        </label>
                        <select
                            id="type"
                            name="type"
                            defaultValue={filters.type ?? ''}
                            className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option value="">Все</option>
                            {meterTypeOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <label
                            htmlFor="reading_from"
                            className="text-sm font-medium text-gray-600"
                        >
                            Дата от
                        </label>
                        <input
                            id="reading_from"
                            name="reading_from"
                            type="date"
                            defaultValue={filters.reading_from ?? ''}
                            className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        />
                    </div>
                    <div className="grid gap-2">
                        <label
                            htmlFor="reading_to"
                            className="text-sm font-medium text-gray-600"
                        >
                            Дата до
                        </label>
                        <input
                            id="reading_to"
                            name="reading_to"
                            type="date"
                            defaultValue={filters.reading_to ?? ''}
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
                            href={renterRoutes.meterReadings()}
                            className="inline-flex h-9 items-center justify-center rounded-md border border-input bg-transparent px-4 text-sm font-medium hover:bg-accent"
                        >
                            Сбросить
                        </Link>
                    </div>
                </Form>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Мои показания</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Тип
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Дата
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Показание
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Пред. показание
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Расход
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Начислено
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Статус
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {meterReadings.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Показаний не найдено
                                    </td>
                                </tr>
                            ) : (
                                meterReadings.map((reading) => (
                                    <tr
                                        key={reading.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {meterTypeLabels[reading.type]}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatDate(reading.reading_date)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatValue(
                                                reading.type,
                                                reading.value,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatOptionalValue(
                                                reading.type,
                                                reading.previous_value,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatOptionalValue(
                                                reading.type,
                                                reading.consumption,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatAmount(
                                                reading.charged_amount,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={cn(
                                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                    statusBadge[reading.status],
                                                )}
                                            >
                                                {
                                                    meterReadingStatusLabels[
                                                        reading.status
                                                    ]
                                                }
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <RenterMeterReadingFormDialog
                open={formOpen}
                onOpenChange={setFormOpen}
            />
        </>
    );
}

RenterMeterReadingsPage.layout = {
    breadcrumbs: [
        {
            title: 'Показания счётчиков',
            href: renterRoutes.meterReadings(),
        },
    ],
};
