import { Head, Link, usePage } from '@inertiajs/react';
import { Edit, Eye, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ExpenseFormDialog from '@/components/expenses/expense-form-dialog';
import ExpenseViewDialog from '@/components/expenses/expense-view-dialog';
import { Button } from '@/components/ui/button';
import { useOpenCreateFromQuery } from '@/hooks/use-open-create-from-query';
import expenses from '@/routes/expenses';
import type { Expense, ExpenseGroup } from '@/types';

type PageProps = {
    expenseGroups: ExpenseGroup[];
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function ExpensesPage() {
    const { expenseGroups } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [viewOpen, setViewOpen] = useState(false);
    const [selectedExpense, setSelectedExpense] = useState<Expense | null>(
        null,
    );

    const openCreate = () => {
        setSelectedExpense(null);
        setFormOpen(true);
    };

    useOpenCreateFromQuery(openCreate);

    const openEdit = (expense: Expense) => {
        setSelectedExpense(expense);
        setFormOpen(true);
    };

    const openView = (expense: Expense) => {
        setSelectedExpense(expense);
        setViewOpen(true);
    };

    return (
        <>
            <Head title="Расходы" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">Расходы</h1>
                    <p className="text-gray-500">
                        Учёт расходов, сгруппированных по дате создания
                    </p>
                </div>
                <Button onClick={openCreate}>
                    <Plus size={20} />
                    Добавить расход
                </Button>
            </div>

            {expenseGroups.length === 0 ? (
                <div className="rounded-xl border border-gray-200 bg-white px-6 py-10 text-center text-gray-500">
                    Расходов пока нет
                </div>
            ) : (
                <div className="space-y-6">
                    {expenseGroups.map((group) => (
                        <div
                            key={group.date}
                            className="rounded-xl border border-gray-200 bg-white"
                        >
                            <div className="flex flex-col gap-1 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <h3 className="font-medium">
                                    {formatDate(group.date)}
                                </h3>
                                <p className="text-sm text-gray-500">
                                    Итого:{' '}
                                    <span className="font-medium text-gray-900">
                                        {formatAmount(group.total_amount)}
                                    </span>
                                </p>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                                Название
                                            </th>
                                            <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                                Сумма
                                            </th>
                                            <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                                Время
                                            </th>
                                            <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                                Действия
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {group.expenses.map((expense) => (
                                            <tr
                                                key={expense.id}
                                                className="border-b border-gray-100 hover:bg-gray-50"
                                            >
                                                <td className="px-6 py-4 font-medium">
                                                    {expense.title}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {formatAmount(
                                                        expense.amount,
                                                    )}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {formatDateTime(
                                                        expense.created_at,
                                                    )}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() =>
                                                                openView(
                                                                    expense,
                                                                )
                                                            }
                                                            aria-label="Просмотр"
                                                        >
                                                            <Eye
                                                                size={18}
                                                                className="text-gray-600"
                                                            />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() =>
                                                                openEdit(
                                                                    expense,
                                                                )
                                                            }
                                                            aria-label="Редактировать"
                                                        >
                                                            <Edit
                                                                size={18}
                                                                className="text-gray-600"
                                                            />
                                                        </Button>
                                                        <Link
                                                            href={expenses.delete(
                                                                expense.id,
                                                            )}
                                                            as="button"
                                                            method="delete"
                                                            preserveScroll
                                                            className="inline-flex size-9 items-center justify-center rounded-md hover:bg-red-50"
                                                            aria-label="Удалить"
                                                        >
                                                            <Trash2
                                                                size={18}
                                                                className="text-red-600"
                                                            />
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            <ExpenseFormDialog
                expense={selectedExpense}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ExpenseViewDialog
                expense={selectedExpense}
                open={viewOpen}
                onOpenChange={setViewOpen}
            />
        </>
    );
}

ExpensesPage.layout = {
    breadcrumbs: [
        {
            title: 'Расходы',
            href: expenses.get(),
        },
    ],
};
