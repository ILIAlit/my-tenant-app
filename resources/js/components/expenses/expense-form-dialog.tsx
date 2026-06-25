import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import expenses from '@/routes/expenses';
import type { Expense } from '@/types';

type Props = {
    expense?: Expense | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function ExpenseFormDialog({
    expense = null,
    open,
    onOpenChange,
}: Props) {
    const isEditing = expense !== null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Редактировать расход' : 'Добавить расход'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    action={
                        isEditing
                            ? expenses.update.url(expense.id)
                            : expenses.create.url()
                    }
                    method={isEditing ? 'put' : 'post'}
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess={!isEditing}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Название *</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    defaultValue={expense?.title ?? ''}
                                    required
                                    placeholder="Ремонт крыши"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="amount">Сумма (BYN) *</Label>
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min={0.01}
                                    step={0.01}
                                    defaultValue={expense?.amount ?? ''}
                                    required
                                    placeholder="150.00"
                                />
                                <InputError message={errors.amount} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Описание</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows={3}
                                    defaultValue={expense?.description ?? ''}
                                    placeholder="Комментарий к расходу"
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                />
                                <InputError message={errors.description} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {isEditing ? 'Сохранить' : 'Создать'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
