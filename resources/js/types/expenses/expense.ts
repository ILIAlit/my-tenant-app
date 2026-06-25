export type Expense = {
    id: number;
    title: string;
    amount: number;
    description: string | null;
    created_at: string;
};

export type ExpenseGroup = {
    date: string;
    total_amount: number;
    expenses: Expense[];
};
