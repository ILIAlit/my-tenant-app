export type Invoices = {
    id: number;
    user_id: number;
    name: string;
    status: 'debt' | 'review' | 'paid';
    total_price: number;
    create_date: string;
    due_date: string;
};
