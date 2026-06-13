import type { InvoiceStatus } from '@/types/invoices/invoices';
import type { PaymentStatus } from '@/types/payments/payments';

export type DashboardUnpaidInvoice = {
    id: number;
    name: string;
    current_status: InvoiceStatus;
    total_price: number;
    paid_price: number;
    remaining: number;
    create_date: string;
    due_date: string;
};

export type DashboardLastPayment = {
    id: number;
    amount: number;
    status: PaymentStatus;
    invoice_name: string | null;
    created_at: string | null;
};

export type DashboardNews = {
    id: number;
    title: string;
    text: string;
    date: string | null;
};

export type DashboardStats = {
    totalDebt: number;
    totalUnpaid: number;
    unpaidCount: number;
    unpaidInvoices: DashboardUnpaidInvoice[];
    lastPayment: DashboardLastPayment | null;
    news: DashboardNews[];
};

export type RoomStatus = 'free' | 'used' | 'repair';

export type DashboardRoom = {
    id: number;
    number: number;
    status: RoomStatus;
    tenant: string | null;
};

export type DashboardFloor = {
    floor: number;
    rooms: DashboardRoom[];
};

export type DashboardRoomStats = {
    total: number;
    free: number;
    used: number;
    repair: number;
};

export type DashboardRecentPayment = {
    id: number;
    amount: number;
    status: PaymentStatus;
    tenant: string | null;
    invoice_name: string | null;
    created_at: string | null;
};

export type DashboardDebtor = {
    user_id: number;
    tenant: string | null;
    debt: number;
    invoices_count: number;
};

export type AdminDashboardStats = {
    floors: DashboardFloor[];
    roomStats: DashboardRoomStats;
    recentPayments: DashboardRecentPayment[];
    debtors: DashboardDebtor[];
};
