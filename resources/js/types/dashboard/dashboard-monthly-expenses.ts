export type DashboardMonthlyExpenses = {
    month: string;
    total_amount: number;
    expense_groups: {
        date: string;
        total_amount: number;
        expenses: {
            id: number;
            title: string;
            amount: number;
            description: string | null;
            created_at: string;
        }[];
    }[];
};
