import { ImageIcon } from 'lucide-react';
import type { RoomUtilityData, UtilityReadingStatus } from '@/types';

type UtilityReadingsHistoryProps = {
    roomsUtilityData: RoomUtilityData[];
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

const MeterPhoto = ({
    label,
    url,
}: {
    label: string;
    url: string | null;
}) => {
    if (!url) {
        return null;
    }

    return (
        <a
            href={url}
            target="_blank"
            rel="noopener noreferrer"
            className="group block overflow-hidden rounded-lg border border-gray-200"
        >
            <img
                src={url}
                alt={label}
                className="h-24 w-full object-cover transition group-hover:scale-105"
            />
            <span className="flex items-center gap-1 px-2 py-1 text-xs text-gray-500">
                <ImageIcon size={12} />
                {label}
            </span>
        </a>
    );
};

export default function UtilityReadingsHistory({
    roomsUtilityData,
}: UtilityReadingsHistoryProps) {
    const roomsWithReadings = roomsUtilityData.filter(
        (room) => room.readings.length > 0,
    );

    if (roomsWithReadings.length === 0) {
        return (
            <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                Показаний пока нет
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {roomsWithReadings.map((roomData) => (
                <div
                    key={roomData.room_id}
                    className="rounded-xl border border-gray-200 bg-white"
                >
                    <div className="border-b border-gray-200 px-6 py-4">
                        <h3 className="font-medium">
                            Комната №{roomData.room_number}
                        </h3>
                    </div>
                    <div className="divide-y divide-gray-100 px-6 py-2">
                        {roomData.readings.map((reading) => {
                            const badge = statusBadge[reading.status];

                            return (
                            <div key={reading.id} className="py-4">
                                <div className="mb-3 flex flex-wrap items-center gap-2">
                                    <p className="text-sm font-medium">
                                        {formatDate(reading.period_start)} —{' '}
                                        {formatDate(reading.period_end)}
                                        {reading.contract &&
                                            ` · Договор №${reading.contract.number}`}
                                    </p>
                                    <span
                                        className={`inline-block rounded-full px-3 py-1 text-xs font-medium ${badge.className}`}
                                    >
                                        {badge.label}
                                    </span>
                                </div>
                                {reading.status === 'rejected' &&
                                    reading.rejection_reason && (
                                        <p className="mb-3 text-sm text-red-600">
                                            {reading.rejection_reason}
                                        </p>
                                    )}
                                <div className="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                                    <div>
                                        <span className="text-gray-500">
                                            Холодная вода
                                        </span>
                                        <p className="font-medium">
                                            {formatReading(reading.cold_water)}{' '}
                                            м³
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">
                                            Горячая вода
                                        </span>
                                        <p className="font-medium">
                                            {formatReading(reading.hot_water)}{' '}
                                            м³
                                        </p>
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
                                    </div>
                                </div>
                                {(reading.cold_water_photo_url ||
                                    reading.hot_water_photo_url ||
                                    reading.electricity_photo_url) && (
                                    <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <MeterPhoto
                                            label="Холодная вода"
                                            url={reading.cold_water_photo_url}
                                        />
                                        <MeterPhoto
                                            label="Горячая вода"
                                            url={reading.hot_water_photo_url}
                                        />
                                        <MeterPhoto
                                            label="Электроэнергия"
                                            url={
                                                reading.electricity_photo_url
                                            }
                                        />
                                    </div>
                                )}
                            </div>
                            );
                        })}
                    </div>
                </div>
            ))}
        </div>
    );
}
