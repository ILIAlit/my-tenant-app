import type { InvoiceRenter } from '@/types/invoices/invoices';

export type PaymentStatus = 'review' | 'approved' | 'rejected';

export type Payment = {
    id: number;
    invoices_id: number;
    amount: number;
    status: PaymentStatus;
    receipt_path: string | null;
    receipt_url: string | null;
    rejection_reason: string | null;
    created_at: string;
    updated_at: string;
    invoice?: {
        id: number;
        name: string;
        total_price?: number;
        paid_price?: number;
        user_id?: number;
        user?: InvoiceRenter | null;
    } | null;
};
