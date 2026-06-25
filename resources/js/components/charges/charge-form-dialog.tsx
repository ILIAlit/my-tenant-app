import { Form, Link } from '@inertiajs/react';
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
import charges from '@/routes/charges';
import type { Charge, ChargeRenterOption } from '@/types';
import { chargeStatusOptions } from '@/types';

type Props = {
    charge?: Charge | null;
    renters: ChargeRenterOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function ChargeFormDialog({
    charge = null,
    renters,
    open,
    onOpenChange,
}: Props) {
    const isEditing = charge !== null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing
                            ? 'Редактировать начисление'
                            : 'Добавить начисление'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    action={
                        isEditing
                            ? charges.update.url(charge.id)
                            : charges.create.url()
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
                                <Label htmlFor="user_id">Арендатор *</Label>
                                <select
                                    title="users"
                                    id="user_id"
                                    name="user_id"
                                    defaultValue={
                                        charge?.user_id ?? renters[0]?.id ?? ''
                                    }
                                    required
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="" disabled>
                                        Выберите арендатора
                                    </option>
                                    {renters.map((renter) => (
                                        <option
                                            key={renter.id}
                                            value={renter.id}
                                        >
                                            {renter.full_name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.user_id} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="total_amount">
                                        Полная сумма (BYN) *
                                    </Label>
                                    <Input
                                        id="total_amount"
                                        name="total_amount"
                                        type="number"
                                        min={0}
                                        step={0.01}
                                        defaultValue={
                                            charge?.total_amount ?? ''
                                        }
                                        required
                                        placeholder="500.00"
                                    />
                                    <InputError message={errors.total_amount} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="paid_amount">
                                        Оплаченная сумма (BYN) *
                                    </Label>
                                    <Input
                                        id="paid_amount"
                                        name="paid_amount"
                                        type="number"
                                        min={0}
                                        step={0.01}
                                        defaultValue={charge?.paid_amount ?? ''}
                                        required
                                        placeholder="250.00"
                                    />
                                    <InputError message={errors.paid_amount} />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="last_payment_date">
                                        Крайняя дата платежа
                                    </Label>
                                    <Input
                                        id="last_payment_date"
                                        name="last_payment_date"
                                        type="date"
                                        defaultValue={
                                            charge?.last_payment_date ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.last_payment_date}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Статус *</Label>
                                    <select
                                        title="users"
                                        id="status"
                                        name="status"
                                        defaultValue={
                                            charge?.status ?? 'unpaid'
                                        }
                                        required
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    >
                                        {chargeStatusOptions.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} />
                                </div>
                            </div>

                            <DialogFooter className="content-betwin gap-2">
                                {isEditing && (
                                    <DialogClose asChild>
                                        <Button type="button" variant="outline">
                                            <Link
                                                href={charges.destroy(
                                                    charge.id,
                                                )}
                                            >
                                                Удалить
                                            </Link>
                                        </Button>
                                    </DialogClose>
                                )}

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
