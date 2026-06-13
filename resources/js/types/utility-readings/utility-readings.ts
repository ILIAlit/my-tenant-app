import type { InvoiceRenter } from '@/types/invoices/invoices';

export type UtilityReadingStatus = 'review' | 'approved' | 'rejected';

export type UtilityReading = {
    id: number;
    rooms_id: number;
    contracts_id: number;
    period_start: string;
    period_end: string;
    cold_water: string | null;
    hot_water: string | null;
    electricity: string | null;
    cold_water_photo_url: string | null;
    hot_water_photo_url: string | null;
    electricity_photo_url: string | null;
    submitted_by: number | null;
    status: UtilityReadingStatus;
    rejection_reason: string | null;
    cold_water_consumption: string | null;
    hot_water_consumption: string | null;
    electricity_consumption: string | null;
    utility_amount: number;
    invoices_id: number | null;
    created_at: string;
    updated_at: string;
    room?: {
        id: number;
        number: number;
        user_id?: number | null;
        user?: InvoiceRenter | null;
    } | null;
    contract?: {
        id: number;
        number: string;
    } | null;
    submitter?: InvoiceRenter | null;
};

export type UtilityReadingPeriod = {
    rooms_id: number;
    room_number: number;
    contracts_id: number;
    contract_number: string;
    period_start: string;
    period_end: string;
    label: string;
};

export type RoomUtilityData = {
    room_id: number;
    room_number: number;
    readings: UtilityReading[];
    availablePeriods: UtilityReadingPeriod[];
};
