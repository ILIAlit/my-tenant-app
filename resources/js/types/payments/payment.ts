export type PaymentStatus = 'pending' | 'approved' | 'rejected';

export type PaymentListItem = {
    id: number;
    amount: number;
    status: PaymentStatus;
    receipt_url: string;
    created_at: string;
    charge: {
        id: number;
        total_amount: number;
        created_at: string;
    };
    renter: {
        id: number;
        full_name: string;
    };
};

export type RenterPaymentItem = {
    id: number;
    amount: number;
    status: PaymentStatus;
    receipt_url: string;
    created_at: string;
    charge: {
        id: number;
        total_amount: number;
        created_at: string;
    };
};

export const paymentStatusLabels: Record<PaymentStatus, string> = {
    pending: 'На рассмотрении',
    approved: 'Подтверждён',
    rejected: 'Отклонён',
};
