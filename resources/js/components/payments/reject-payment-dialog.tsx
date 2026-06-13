import { Form } from '@inertiajs/react';
import AdminPaymentsController from '@/actions/App/Http/Controllers/Admin/Payments/AdminPaymentsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import type { Payment } from '@/types';

type RejectPaymentDialogProps = {
    payment: Payment;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RejectPaymentDialog({
    payment,
    open,
    onOpenChange,
}: RejectPaymentDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Отклонить платёж</DialogTitle>
                    <DialogDescription>
                        Платёж на {payment.amount} ₽ по начислению «
                        {payment.invoice?.name ?? '—'}». Сумма будет возвращена к
                        долгу.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...AdminPaymentsController.rejectPayment.form(payment.id)}
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="rejection_reason">
                                    Причина отклонения *
                                </Label>
                                <textarea
                                    id="rejection_reason"
                                    name="rejection_reason"
                                    rows={3}
                                    required
                                    className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                    placeholder="Например: чек не читается"
                                />
                                <InputError message={errors.rejection_reason} />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    Отклонить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
