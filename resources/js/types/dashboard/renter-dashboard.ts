import type { ChargeDisplayStatus } from '../charges/charge';
import type { DashboardNewsItem } from './dashboard-feed';
import type { RenterChargeItem } from '../charges/charge';

import type { RoomSummary } from '../rooms/room';

export type RenterDashboardSummary = {
    due_amount: number;
    pay_charge: RenterChargeItem | null;
    debt_amount: number;
    last_payment: {
        date: string;
        amount: number;
    } | null;
    next_charge: {
        date: string;
        days_until: number;
    } | null;
    has_contract: boolean;
    room: RoomSummary | null;
    room_status: string;
    room_status_hint: string;
};

export type RenterDashboardMonthlyCharge = {
    id: number;
    label: string;
    period: string;
    total_amount: number;
    display_status: ChargeDisplayStatus;
};

export type RenterDashboardMonthlyCharges = {
    month: string;
    month_label: string;
    charges: RenterDashboardMonthlyCharge[];
    total_to_pay: number;
    pay_charge: RenterChargeItem | null;
};

export type RenterDashboardPaymentHistoryItem = {
    id: number;
    date: string;
    service: string;
    amount: number;
    status: string;
    status_label: string;
};

export type RenterDashboardMeterReading = {
    type: string;
    label: string;
    previous_value: number | null;
    current_value: number | null;
    consumption: number | null;
    reading_date: string | null;
};

export type RenterDashboardUsefulLink = {
    title: string;
    description: string;
    url: string;
};

export type RenterDashboard = {
    summary: RenterDashboardSummary;
    monthly_charges: RenterDashboardMonthlyCharges;
    payment_history: RenterDashboardPaymentHistoryItem[];
    meter_readings: RenterDashboardMeterReading[];
    news: DashboardNewsItem[];
    useful_links: RenterDashboardUsefulLink[];
};
