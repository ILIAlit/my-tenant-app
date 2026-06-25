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
import renterRoutes from '@/routes/renter';
import type { RenterChargeItem } from '@/types';

type Props = {
    charge: RenterChargeItem | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function PaymentFormDialog({
    charge,
    open,
    onOpenChange,
}: Props) {
    if (charge === null) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Оплата начисления</DialogTitle>
                </DialogHeader>

                <Form
                    action={renterRoutes.payments.store.url(charge.id)}
                    method="post"
                    encType="multipart/form-data"
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess
                >
                    {({ processing, errors }) => (
                        <>
                            <p className="text-sm text-muted-foreground">
                                Остаток к оплате:{' '}
                                <span className="font-medium text-foreground">
                                    {charge.remaining_amount.toFixed(2)} BYN
                                </span>
                            </p>

                            <div className="grid gap-2">
                                <Label htmlFor="amount">Сумма (BYN) *</Label>
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min={0.01}
                                    max={charge.remaining_amount}
                                    step={0.01}
                                    defaultValue={
                                        charge.remaining_amount.toFixed(2)
                                    }
                                    required
                                    placeholder="100.00"
                                />
                                <InputError message={errors.amount} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="receipt">Фото чека *</Label>
                                <Input
                                    id="receipt"
                                    name="receipt"
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp,image/*"
                                    required
                                />
                                <p className="text-xs text-muted-foreground">
                                    JPG, PNG или WEBP, до 10 МБ
                                </p>
                                <InputError message={errors.receipt} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Отправить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
