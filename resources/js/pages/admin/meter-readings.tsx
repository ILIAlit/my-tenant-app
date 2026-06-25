import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, Edit, Plus, X } from 'lucide-react';
import { useState } from 'react';
import MeterReadingFormDialog from '@/components/meter-readings/meter-reading-form-dialog';
import MeterTariffsForm from '@/components/meter-readings/meter-tariffs-form';
import { Button } from '@/components/ui/button';
import { useOpenCreateFromQuery } from '@/hooks/use-open-create-from-query';
import { cn } from '@/lib/utils';
import meterReadings from '@/routes/meter-readings';
import renters from '@/routes/renters';
import type {
    MeterReading,
    MeterReadingRenterOption,
    MeterReadingStatus,
    MeterTariffsByRoomType,
    MeterType,
} from '@/types';
import { meterReadingStatusLabels, meterTypeLabels, roomWithFloorLabel } from '@/types';

type PageProps = {
    meterReadings: MeterReading[];
    renters: MeterReadingRenterOption[];
    tariffs: MeterTariffsByRoomType;
    showArchive: boolean;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });

const formatRoom = (reading: MeterReading): string => {
    if (reading.renter.room_label) {
        return reading.renter.room_label;
    }

    if (!reading.renter.room) {
        return '—';
    }

    return roomWithFloorLabel(reading.renter.room);
};

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
    archived: 'bg-gray-100 text-gray-600',
};

export default function MeterReadingsPage() {
    const {
        meterReadings: readingsList,
        renters: rentersList,
        tariffs,
        showArchive,
    } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [selectedReading, setSelectedReading] = useState<MeterReading | null>(
        null,
    );

    const openCreate = () => {
        setSelectedReading(null);
        setFormOpen(true);
    };

    useOpenCreateFromQuery(openCreate);

    const openEdit = (reading: MeterReading) => {
        setSelectedReading(reading);
        setFormOpen(true);
    };

    return (
        <>
            <Head title="Показания счётчиков" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">
                        Показания счётчиков
                    </h1>
                    <p className="text-gray-500">
                        Учёт показаний горячей и холодной воды, электричества
                    </p>
                </div>
                {!showArchive && (
                    <Button
                        onClick={openCreate}
                        disabled={rentersList.length === 0}
                    >
                        <Plus size={20} />
                        Добавить показание
                    </Button>
                )}
            </div>

            {!showArchive && (
                <div className="mb-6 rounded-xl border border-gray-200 bg-white">
                    <div className="border-b border-gray-200 px-6 py-4">
                        <h3 className="font-medium">Тарифы</h3>
                        <p className="text-sm text-gray-500">
                            Отдельные тарифы для комнат и гаражей
                        </p>
                    </div>
                    <div className="p-6">
                        <MeterTariffsForm tariffs={tariffs} />
                    </div>
                </div>
            )}

            <div className="mb-4 flex gap-2">
                <Link
                    href={meterReadings.get()}
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
                    href={meterReadings.get({ query: { archive: '1' } })}
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
                            ? 'Архивные показания'
                            : 'Список показаний'}
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
                                    Сумма
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
                            {readingsList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={showArchive ? 9 : 10}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        {showArchive
                                            ? 'Архивных показаний нет'
                                            : 'Показаний пока нет'}
                                    </td>
                                </tr>
                            ) : (
                                readingsList.map((reading) => (
                                    <tr
                                        key={reading.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {reading.renter.id !== null ? (
                                                <Link
                                                    href={renters.settings(
                                                        reading.renter.id,
                                                    )}
                                                    className="font-medium hover:underline"
                                                >
                                                    {reading.renter.full_name}
                                                </Link>
                                            ) : (
                                                <span className="font-medium">
                                                    {reading.renter.full_name}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatRoom(reading)}
                                        </td>
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
                                                reading.estimated_cost,
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
                                        {!showArchive && (
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    {reading.status ===
                                                    'pending' ? (
                                                        <>
                                                            <Form
                                                                {...meterReadings.approve.form(
                                                                    reading.id,
                                                                )}
                                                                options={{
                                                                    preserveScroll: true,
                                                                }}
                                                            >
                                                                {({
                                                                    processing,
                                                                }) => (
                                                                    <Button
                                                                        type="submit"
                                                                        size="sm"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        <Check
                                                                            size={
                                                                                16
                                                                            }
                                                                        />
                                                                        Подтвердить
                                                                    </Button>
                                                                )}
                                                            </Form>
                                                            <Form
                                                                {...meterReadings.reject.form(
                                                                    reading.id,
                                                                )}
                                                                options={{
                                                                    preserveScroll: true,
                                                                }}
                                                            >
                                                                {({
                                                                    processing,
                                                                }) => (
                                                                    <Button
                                                                        type="submit"
                                                                        size="sm"
                                                                        variant="outline"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        <X
                                                                            size={
                                                                                16
                                                                            }
                                                                        />
                                                                        Отклонить
                                                                    </Button>
                                                                )}
                                                            </Form>
                                                        </>
                                                    ) : (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                openEdit(
                                                                    reading,
                                                                )
                                                            }
                                                        >
                                                            <Edit size={16} />
                                                            Изменить
                                                        </Button>
                                                    )}
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
                <MeterReadingFormDialog
                    meterReading={selectedReading}
                    renters={rentersList}
                    open={formOpen}
                    onOpenChange={setFormOpen}
                />
            )}
        </>
    );
}

MeterReadingsPage.layout = {
    breadcrumbs: [
        {
            title: 'Показания счётчиков',
            href: meterReadings.get(),
        },
    ],
};
