export type Rooms = {
    id: number;
    number: number;
    floor: number;
    square: number;
    status: 'free' | 'used' | 'repair';
    date_of_last_repair: string | null;
    notes: string | null;
    user_id: number | null;
    created_at: string;
    updated_at: string;
};
