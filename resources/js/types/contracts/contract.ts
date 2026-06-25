import type { RoomSummary } from '../rooms/room';

export type ContractListItem = {
    id: number;
    number: string;
    start_date: string;
    end_date: string | null;
    monthly_rent: number;
    notes: string | null;
    file_url: string | null;
    file_name: string | null;
    is_image: boolean;
    renter: {
        id: number;
        full_name: string;
        room: RoomSummary | null;
    };
};
