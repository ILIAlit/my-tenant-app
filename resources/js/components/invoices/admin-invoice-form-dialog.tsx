import { Form } from '@inertiajs/react';
import AdminInvoicesController from '@/actions/App/Http/Controllers/Admin/Invoices/AdminInvoicesController';
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
import type { InvoiceRenter, Invoices } from '@/types';

type AdminInvoiceFormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    renters: InvoiceRenter[];
    invoice?: Invoices | null;
};

const renterName = (renter: InvoiceRenter) =>
    [renter.last_name, renter.name, renter.middle_name]
        .filter(Boolean)
        .join(' ');

const today = () => new Date().toISOString().split('T')[0];

export default function AdminInvoiceFormDialog({
    open,
    onOpenChange,
    renters,
    invoice = null,
}: AdminInvoiceFormDialogProps) {
    const isEdit = Boolean(invoice);

    const formProps = isEdit
        ? AdminInvoicesController.updateInvoice.form(invoice!.id)
        : AdminInvoicesController.createInvoice.form();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEdit
                            ? 'Редактировать начисление'
                            : 'Создать начисление'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    key={invoice?.id ?? 'create'}
                    {...formProps}
                    options={{ preserveScroll: true }}
                    className="space-y-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="user_id">Арендатор *</Label>
                                {isEdit ? (
                                    <Input
                                        value={
                                            invoice?.user
                                                ? renterName(invoice.user)
                                                : `#${invoice?.user_id}`
                                        }
                                        readOnly
                                        disabled
                                    />
                                ) : (
                                    <select
                                        id="user_id"
                                        name="user_id"
                                        className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                        required
                                        defaultValue=""
                                    >
                                        <option value="" disabled>
                                            Выберите арендатора
                                        </option>
                                        {renters.map((renter) => (
                                            <option
                                                key={renter.id}
                                                value={renter.id}
                                            >
                                                {renterName(renter)}
                                            </option>
                                        ))}
                                    </select>
                                )}
                                <InputError message={errors.user_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Название *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    defaultValue={invoice?.name ?? ''}
                                    placeholder="Коммунальные услуги, июнь"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="total_price">Сумма (₽) *</Label>
                                <Input
                                    id="total_price"
                                    name="total_price"
                                    type="number"
                                    min="0"
                                    required
                                    defaultValue={invoice?.total_price ?? ''}
                                    placeholder="5000"
                                />
                                <InputError message={errors.total_price} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="create_date">
                                    Дата начисления *
                                </Label>
                                <Input
                                    id="create_date"
                                    name="create_date"
                                    type="date"
                                    required
                                    defaultValue={invoice?.create_date ?? today()}
                                />
                                <InputError message={errors.create_date} />
                                <p className="text-sm text-gray-500">
                                    Срок оплаты рассчитывается автоматически по
                                    договору арендатора: до того же числа
                                    следующего месяца.
                                </p>
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button disabled={processing} type="submit">
                                    {isEdit ? 'Сохранить' : 'Создать'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
