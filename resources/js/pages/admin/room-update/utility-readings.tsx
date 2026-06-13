import { Link, usePage } from '@inertiajs/react';
import { Check, Edit, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import AdminUtilityReadingsController from '@/actions/App/Http/Controllers/Admin/UtilityReadings/AdminUtilityReadingsController';
import AdminUtilityReadingFormDialog from '@/components/utility-readings/admin-utility-reading-form-dialog';
import RejectUtilityReadingDialog from '@/components/utility-readings/reject-utility-reading-dialog';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import type { Rooms, UtilityReading, UtilityReadingStatus } from '@/types';

type PageProps = {
    room: Rooms;
    readings: UtilityReading[];
};

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

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

const formatReading = (value: string | null) => (value !== null ? value : '—');

export default function AdminRoomUtilityReadingsPage() {
    const { room, readings } = usePage<PageProps>().props;
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<UtilityReading | null>(null);
    const [rejecting, setRejecting] = useState<UtilityReading | null>(null);

    const openEdit = (reading: UtilityReading) => {
        setEditing(reading);
        setDialogOpen(true);
    };

    return (
        <>
            <PageHeader
                title="Показания счётчиков"
                description={`Показания коммунальных услуг комнаты №${room.number}`}
            />

            {readings.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    Показаний пока нет
                </div>
            ) : (
                <div className="space-y-4">
                    {readings.map((reading) => {
                        const badge = statusBadge[reading.status];
                        const isPending = reading.status === 'review';

                        return (
                            <div
                                key={reading.id}
                                className="rounded-xl border border-gray-200 bg-white"
                            >
                                <div className="flex flex-col gap-3 border-b border-gray-200 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="font-medium">
                                                {formatDate(
                                                    reading.period_start,
                                                )}{' '}
                                                —{' '}
                                                {formatDate(reading.period_end)}
                                            </h3>
                                            <span
                                                className={`inline-block rounded-full px-3 py-1 text-xs font-medium ${badge.className}`}
                                            >
                                                {badge.label}
                                            </span>
                                        </div>
                                        {reading.contract && (
                                            <p className="text-sm text-gray-500">
                                                Договор №
                                                {reading.contract.number}
                                            </p>
                                        )}
                                        {reading.status === 'rejected' &&
                                            reading.rejection_reason && (
                                                <p className="mt-1 text-sm text-red-600">
                                                    {reading.rejection_reason}
                                                </p>
                                            )}
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2">
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
                                                    <Check size={14} />
                                                    Одобрить
                                                </Link>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="gap-1 text-red-600"
                                                    onClick={() =>
                                                        setRejecting(reading)
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
                                            onClick={() => openEdit(reading)}
                                        >
                                            <Edit size={14} />
                                            Изменить
                                        </Button>
                                        <Link
                                            href={AdminUtilityReadingsController.delete(
                                                [reading.id, room.id],
                                            )}
                                            as="button"
                                            method="delete"
                                            preserveScroll
                                            className="rounded-lg bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
                                        >
                                            <Trash2 size={14} />
                                        </Link>
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 gap-4 p-5 text-sm sm:grid-cols-3">
                                    <div>
                                        <span className="text-gray-500">
                                            Холодная вода
                                        </span>
                                        <p className="font-medium">
                                            {formatReading(reading.cold_water)}{' '}
                                            м³
                                        </p>
                                        {reading.cold_water_photo_url && (
                                            <a
                                                href={
                                                    reading.cold_water_photo_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-xs text-primary underline"
                                            >
                                                Фото счётчика
                                            </a>
                                        )}
                                    </div>
                                    <div>
                                        <span className="text-gray-500">
                                            Горячая вода
                                        </span>
                                        <p className="font-medium">
                                            {formatReading(reading.hot_water)}{' '}
                                            м³
                                        </p>
                                        {reading.hot_water_photo_url && (
                                            <a
                                                href={
                                                    reading.hot_water_photo_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-xs text-primary underline"
                                            >
                                                Фото счётчика
                                            </a>
                                        )}
                                    </div>
                                    <div>
                                        <span className="text-gray-500">
                                            Электроэнергия
                                        </span>
                                        <p className="font-medium">
                                            {formatReading(
                                                reading.electricity,
                                            )}{' '}
                                            кВт·ч
                                        </p>
                                        {reading.electricity_photo_url && (
                                            <a
                                                href={
                                                    reading.electricity_photo_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-xs text-primary underline"
                                            >
                                                Фото счётчика
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            <AdminUtilityReadingFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                reading={editing}
                fromRoom
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
