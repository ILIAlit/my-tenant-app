export type NotificationItem = {
    id: string;
    type: string;
    title: string;
    message: string;
    url: string | null;
    created_at: string;
};

export type NotificationsData = {
    unread_count: number;
    items: NotificationItem[];
};
