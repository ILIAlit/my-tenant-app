export type InvoiceStatus = 'debt' | 'review' | 'paid';

export type InvoiceRenter = {
    id: number;
    name: string;
    last_name: string;
    middle_name: string;
};

export type Invoices = {
    id: number;
    user_id: number;
    name: string;
    status: InvoiceStatus;
    current_status: InvoiceStatus;
    total_price: number;
    paid_price: number;
    create_date: string;
    due_date: string;
    user?: InvoiceRenter | null;
};
