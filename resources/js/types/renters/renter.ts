import type { MeterType } from '../meter-readings/meter-reading';
import type { RoomSummary, RoomType } from '../rooms/room';

export type RenterRoom = RoomSummary & {
    id: number;
};

export type RenterRentalRoom = RoomSummary & {
    area: number;
};

export type AssignableRoom = RoomSummary & {
    id: number;
    status: 'free' | 'repair' | 'occupied';
    user_id: number | null;
};

export type RenterContract = {
    id: number;
    number: string;
    start_date: string;
    end_date: string | null;
    monthly_rent: number;
    notes: string | null;
    file_url: string | null;
    file_name: string | null;
    is_image: boolean;
};

export type RenterInitialMeterReading = {
    value: number;
    reading_date: string;
};

export type RenterInitialMeterReadings = Record<
    MeterType,
    RenterInitialMeterReading | null
>;

export type RenterServiceItem = {
    id: number;
    name: string;
    price: number;
    is_active: boolean;
    notes: string | null;
};
