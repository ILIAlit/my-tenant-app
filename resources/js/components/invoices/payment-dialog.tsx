import { Form } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import invoices from '@/routes/invoices';
import type { Invoices } from '@/types';

type PaymentDialogProps = {
    invoice: Invoices;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function PaymentDialog({
    invoice,
    open,
    onOpenChange,
}: PaymentDialogProps) {
    const [mode, setMode] = useState<'full' | 'partial'>('full');
    const [partialAmount, setPartialAmount] = useState('');

    const handleClose = () => {
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'full' ? 'Оплата счета' : 'Оплата частями'}
                    </DialogTitle>
                    <DialogDescription>Счет: {invoice.name}</DialogDescription>
                </DialogHeader>
                <Form
                    action={invoices.paymentProcess()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="space-y-4">
                                <div>
                                    <Label className="text-sm text-muted-foreground">
                                        Сумма к оплате
                                    </Label>
                                    <div className="mt-2 rounded-lg border bg-muted p-3">
                                        <span className="text-lg font-semibold">
                                            {mode === 'full'
                                                ? invoice.total_price -
                                                  invoice.paid_price
                                                : partialAmount || '0'}{' '}
                                            ₽
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    {mode === 'partial' && (
                                        <Label htmlFor="partial-amount">
                                            Сумма частичной оплаты
                                        </Label>
                                    )}
                                    <Input
                                        hidden
                                        id="invoices_id"
                                        type="number"
                                        name="invoices_id"
                                        value={invoice.id}
                                    />
                                    <Input
                                        id="amount"
                                        type="number"
                                        name="amount"
                                        placeholder="Введите сумму"
                                        value={
                                            mode === 'partial'
                                                ? partialAmount
                                                : invoice.total_price -
                                                  invoice.paid_price
                                        }
                                        onChange={(e) =>
                                            setPartialAmount(e.target.value)
                                        }
                                        min="0.01"
                                        max={
                                            invoice.total_price -
                                            invoice.paid_price
                                        }
                                        step="0.01"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.amount}
                                    />
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Максимальная сумма:{' '}
                                        {invoice.total_price -
                                            invoice.paid_price}
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="receipt">
                                        Прикрепить чек (фото/скриншот)
                                    </Label>
                                    <Input
                                        id="receipt"
                                        type="file"
                                        name="receipt"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.receipt}
                                    />
                                </div>

                                {mode === 'full' && (
                                    <div className="border-t pt-4">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setMode('partial')}
                                            className="w-full"
                                        >
                                            Оплатить частями
                                        </Button>
                                    </div>
                                )}

                                {mode === 'partial' && (
                                    <div className="border-t pt-4">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => {
                                                setMode('full');
                                                setPartialAmount('');
                                            }}
                                            className="w-full"
                                        >
                                            Вернуться к полной оплате
                                        </Button>
                                    </div>
                                )}
                            </div>

                            <DialogFooter>
                                <Button variant="outline" onClick={handleClose}>
                                    Отмена
                                </Button>
                                <Button
                                    //onClick={handleClose}
                                    disabled={processing}
                                >
                                    Оплатить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
