import type { RoomSummary } from '../rooms/room';
import type { MeterReadingStatus, MeterType } from '../meter-readings/meter-reading';
import type { PaymentListItem } from '../payments/payment';

export type DashboardRecentMeterReading = {
    id: number;
    type: MeterType;
    reading_date: string;
    value: number;
    status: MeterReadingStatus;
    consumption: number | null;
    renter: {
        id: number;
        full_name: string;
        room: RoomSummary | null;
    };
};

export type DashboardRecentRecords = {
    recentPayments: PaymentListItem[];
    recentMeterReadings: DashboardRecentMeterReading[];
};
