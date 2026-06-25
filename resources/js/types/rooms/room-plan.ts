import type { RoomType } from './room';

export type RoomPlanDisplayStatus =
    | 'free'
    | 'occupied'
    | 'repair'
    | 'debt'
    | 'awaiting_payment';

export type HousePlanRoom = {
    id: number;
    type: RoomType;
    number: string;
    floor: number | null;
    area: number;
    display_status: RoomPlanDisplayStatus;
    renter_name: string | null;
};

export type HousePlan = {
    floors: number[];
    rooms: HousePlanRoom[];
};

export const roomPlanDisplayStatusLabels: Record<
    RoomPlanDisplayStatus,
    string
> = {
    free: 'Свободна',
    occupied: 'Занята',
    repair: 'Ремонт',
    debt: 'Долг',
    awaiting_payment: 'Ожидает оплаты',
};

export const roomPlanStatusBadge: Record<RoomPlanDisplayStatus, string> = {
    free: 'bg-green-100 text-green-800 border-green-200',
    occupied: 'bg-blue-100 text-blue-800 border-blue-200',
    repair: 'bg-amber-100 text-amber-800 border-amber-200',
    debt: 'bg-red-100 text-red-800 border-red-200',
    awaiting_payment: 'bg-violet-100 text-violet-800 border-violet-200',
};

export const roomPlanStatusCard: Record<RoomPlanDisplayStatus, string> = {
    free: 'border-green-200 bg-green-50/50',
    occupied: 'border-blue-200 bg-blue-50/50',
    repair: 'border-amber-200 bg-amber-50/50',
    debt: 'border-red-200 bg-red-50/50',
    awaiting_payment: 'border-violet-200 bg-violet-50/50',
};
