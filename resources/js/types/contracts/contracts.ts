import type { InvoiceRenter } from '@/types/invoices/invoices';

export type Contract = {
    id: number;
    rooms_id: number;
    number: string;
    conclusion_date: string;
    expiration_date: string;
    payment_terms: string;
    termination_terms: string;
    file_path: string | null;
    file_url: string | null;
    created_at: string;
    updated_at: string;
    room?: {
        id: number;
        number: number;
        user_id?: number | null;
        user?: InvoiceRenter | null;
    } | null;
};
