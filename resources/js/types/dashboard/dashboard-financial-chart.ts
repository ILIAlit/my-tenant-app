export type DashboardFinancialChartCategory = {
    category: string;
    label: string;
    amount: number;
    percentage: number;
};

export type DashboardFinancialChart = {
    month: string;
    total_amount: number;
    categories: DashboardFinancialChartCategory[];
};
