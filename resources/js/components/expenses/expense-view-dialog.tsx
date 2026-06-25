import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Expense } from '@/types';

type Props = {
    expense: Expense | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function ExpenseViewDialog({
    expense,
    open,
    onOpenChange,
}: Props) {
    if (!expense) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{expense.title}</DialogTitle>
                </DialogHeader>

                <dl className="space-y-3 text-sm">
                    <div>
                        <dt className="text-muted-foreground">Сумма</dt>
                        <dd className="font-medium">
                            {formatAmount(expense.amount)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Дата создания</dt>
                        <dd>{formatDateTime(expense.created_at)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Описание</dt>
                        <dd className="whitespace-pre-wrap">
                            {expense.description || '—'}
                        </dd>
                    </div>
                </dl>
            </DialogContent>
        </Dialog>
    );
}
