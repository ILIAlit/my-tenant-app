import type { RoomSummary } from '../rooms/room';

export type ChargeStatus = 'paid' | 'pending' | 'unpaid' | 'debt' | 'archived';

export type ChargeDisplayStatus = ChargeStatus;

export type ChargeCategory =
    | 'rent'
    | 'utilities'
    | 'cold_water'
    | 'hot_water'
    | 'electricity'
    | 'sewage'
    | 'other';

export type UtilitiesChargeBreakdownItem = {
    key: string;
    label: string;
    consumption: number;
    unit: string;
    tariff: number;
    amount: number;
};

export type ChargeRenter = {
    id: number | null;
    full_name: string;
    room: RoomSummary | null;
    room_label?: string | null;
};

export type Charge = {
    id: number;
    user_id: number | null;
    category: ChargeCategory;
    total_amount: number;
    paid_amount: number;
    last_payment_date: string | null;
    status: ChargeStatus;
    display_status: ChargeDisplayStatus;
    breakdown: UtilitiesChargeBreakdownItem[] | null;
    renter: ChargeRenter;
};

export type ChargeRenterOption = {
    id: number;
    full_name: string;
};

export type RenterChargeItem = {
    id: number;
    category: ChargeCategory;
    total_amount: number;
    paid_amount: number;
    remaining_amount: number;
    can_pay: boolean;
    last_payment_date: string | null;
    status: ChargeStatus;
    display_status: ChargeDisplayStatus;
    created_at: string;
    breakdown: UtilitiesChargeBreakdownItem[] | null;
};

export type ChargeDateFilters = {
    created_from: string | null;
    created_to: string | null;
};

export const chargeCategoryLabels: Record<ChargeCategory, string> = {
    rent: 'Аренда и услуги',
    utilities: 'Коммунальные услуги',
    cold_water: 'Холодная вода',
    hot_water: 'Горячая вода',
    electricity: 'Электричество',
    sewage: 'Канализация',
    other: 'Прочее',
};

export const chargeCategoryChartColors: Record<ChargeCategory, string> = {
    rent: '#2563eb',
    utilities: '#16a34a',
    cold_water: '#0891b2',
    hot_water: '#ea580c',
    electricity: '#ca8a04',
    sewage: '#9333ea',
    other: '#64748b',
};

export const chargeDisplayStatusLabels: Record<ChargeDisplayStatus, string> = {
    paid: 'Оплачено',
    pending: 'На рассмотрении',
    unpaid: 'Не оплачено',
    debt: 'Долг',
    archived: 'Архив',
};

export const chargeStatusLabels: Record<ChargeStatus, string> = {
    paid: chargeDisplayStatusLabels.paid,
    pending: chargeDisplayStatusLabels.pending,
    unpaid: chargeDisplayStatusLabels.unpaid,
    debt: chargeDisplayStatusLabels.debt,
    archived: chargeDisplayStatusLabels.archived,
};

export const chargeStatusOptions: { value: ChargeStatus; label: string }[] = [
    { value: 'paid', label: 'Оплачено' },
    { value: 'pending', label: 'На рассмотрении' },
    { value: 'unpaid', label: 'Не оплачено' },
    { value: 'debt', label: 'Долг' },
];
