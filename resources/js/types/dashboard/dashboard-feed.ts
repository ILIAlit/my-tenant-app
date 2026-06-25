export type DashboardNotificationItem = {
    id: string;
    type: string;
    title: string;
    message: string;
    url: string | null;
    created_at: string;
    read_at: string | null;
};

export type DashboardNotificationsFeed = {
    unread_count: number;
    items: DashboardNotificationItem[];
};

export type DashboardNewsItem = {
    id: number;
    title: string;
    text: string;
    date: string;
};
