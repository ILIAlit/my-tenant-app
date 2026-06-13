export type AppNotificationType =
    | 'payment'
    | 'utility_reading'
    | 'invoice_due_soon'
    | 'utility_reading_due_soon';

export type AppNotificationData = {
    type: AppNotificationType;
    status: string;
    title: string;
    message: string;
    url: string;
    amount?: number;
    invoice_id?: number;
    invoice_name?: string;
    due_date?: string;
    days_left?: number;
    rooms_id?: number;
    room_number?: number;
    period?: string;
    utility_amount?: number;
    rejection_reason?: string | null;
};

export type AppNotification = {
    id: string;
    data: AppNotificationData;
    read_at: string | null;
    created_at: string | null;
};

export type NotificationsShared = {
    items: AppNotification[];
    unread_count: number;
};
