export type RoomStatus = 'free' | 'repair' | 'occupied';

export type RoomType = 'room' | 'garage';

export type RoomSummary = {
    type: RoomType;
    number: string;
    floor: number | null;
};

export type Room = {
    id: number;
    type: RoomType;
    number: string;
    floor: number | null;
    area: number;
    status: RoomStatus;
    last_repair_date: string | null;
    notes: string | null;
};

export const roomTypeLabels: Record<RoomType, string> = {
    room: 'Комната',
    garage: 'Гараж',
};

export const roomTypeOptions: { value: RoomType; label: string }[] = [
    { value: 'room', label: 'Комната' },
    { value: 'garage', label: 'Гараж' },
];

export const roomStatusLabels: Record<RoomStatus, string> = {
    free: 'Свободна',
    repair: 'Ремонт',
    occupied: 'Занята',
};

export const roomStatusOptions: { value: RoomStatus; label: string }[] = [
    { value: 'free', label: 'Свободна' },
    { value: 'repair', label: 'Ремонт' },
    { value: 'occupied', label: 'Занята' },
];

export function roomNumberLabel(room: { type: RoomType; number: string }): string {
    return `${roomTypeLabels[room.type]} ${room.number}`;
}

export function formatRoomFloor(floor: number | null): string {
    return floor === null ? '—' : String(floor);
}

export function roomWithFloorLabel(room: {
    type: RoomType;
    number: string;
    floor: number | null;
}): string {
    if (room.floor === null) {
        return roomNumberLabel(room);
    }

    return `${roomNumberLabel(room)} (эт. ${room.floor})`;
}
