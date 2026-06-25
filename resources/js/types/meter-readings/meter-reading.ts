import type { RoomSummary, RoomType } from '../rooms/room';

export type MeterType = 'hot_water' | 'cold_water' | 'electricity';

export type MeterTariffType = MeterType | 'sewage';

export type MeterTariffs = Record<MeterTariffType, number>;

export type MeterTariffsByRoomType = Record<RoomType, MeterTariffs>;

export type MeterReadingStatus = 'pending' | 'approved' | 'rejected' | 'archived';

export type MeterReadingRenter = {
    id: number | null;
    full_name: string;
    room: RoomSummary | null;
    room_label?: string | null;
};

export type MeterReading = {
    id: number;
    user_id: number | null;
    type: MeterType;
    reading_date: string;
    value: number;
    status: MeterReadingStatus;
    previous_value: number | null;
    consumption: number | null;
    estimated_cost: number | null;
    renter: MeterReadingRenter;
};

export type MeterReadingRenterOption = {
    id: number;
    full_name: string;
};

export type RenterMeterReadingItem = {
    id: number;
    type: MeterType;
    reading_date: string;
    value: number;
    status: MeterReadingStatus;
    previous_value: number | null;
    consumption: number | null;
    charged_amount: number | null;
};

export type MeterReadingFilters = {
    reading_from: string | null;
    reading_to: string | null;
    type: MeterType | null;
};

export const meterTariffLabels: Record<MeterTariffType, string> = {
    hot_water: 'Горячая вода',
    cold_water: 'Холодная вода',
    electricity: 'Электричество',
    sewage: 'Канализация',
};

export const meterTypeLabels: Record<MeterType, string> = {
    hot_water: meterTariffLabels.hot_water,
    cold_water: meterTariffLabels.cold_water,
    electricity: meterTariffLabels.electricity,
};

export const meterTypeOptions: { value: MeterType; label: string }[] = [
    { value: 'hot_water', label: 'Горячая вода' },
    { value: 'cold_water', label: 'Холодная вода' },
    { value: 'electricity', label: 'Электричество' },
];

export const meterReadingStatusLabels: Record<MeterReadingStatus, string> = {
    pending: 'На рассмотрении',
    approved: 'Подтверждено',
    rejected: 'Отклонено',
    archived: 'Архив',
};
