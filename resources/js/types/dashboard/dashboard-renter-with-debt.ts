import type { RoomSummary } from '../rooms/room';

export type DashboardRenterWithDebt = {
    id: number;
    full_name: string;
    debt_amount: number;
    room: RoomSummary | null;
};
