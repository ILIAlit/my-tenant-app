import { Form } from '@inertiajs/react';
import AdminContractsController from '@/actions/App/Http/Controllers/Admin/Contracts/AdminContractsController';
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
import type { Contract } from '@/types';

type AdminContractFormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    roomId: number;
    contract?: Contract | null;
};

export default function AdminContractFormDialog({
    open,
    onOpenChange,
    roomId,
    contract = null,
}: AdminContractFormDialogProps) {
    const isEdit = Boolean(contract);

    const formProps = isEdit
        ? AdminContractsController.update.form(contract!.id)
        : AdminContractsController.create.form();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Редактировать договор' : 'Добавить договор'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    key={contract?.id ?? 'create'}
                    {...formProps}
                    options={{ preserveScroll: true }}
                    className="space-y-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            {!isEdit && (
                                <input
                                    type="hidden"
                                    name="rooms_id"
                                    value={roomId}
                                />
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="number">Номер договора *</Label>
                                <Input
                                    id="number"
                                    name="number"
                                    type="text"
                                    required
                                    defaultValue={contract?.number ?? ''}
                                    placeholder="Д-2026/001"
                                />
                                <InputError message={errors.number} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="conclusion_date">
                                        Дата заключения *
                                    </Label>
                                    <Input
                                        id="conclusion_date"
                                        name="conclusion_date"
                                        type="date"
                                        required
                                        defaultValue={
                                            contract?.conclusion_date ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.conclusion_date}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="expiration_date">
                                        Срок действия *
                                    </Label>
                                    <Input
                                        id="expiration_date"
                                        name="expiration_date"
                                        type="date"
                                        required
                                        defaultValue={
                                            contract?.expiration_date ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.expiration_date}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="payment_terms">
                                    Условия оплаты *
                                </Label>
                                <textarea
                                    id="payment_terms"
                                    name="payment_terms"
                                    rows={3}
                                    required
                                    className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                    defaultValue={contract?.payment_terms ?? ''}
                                    placeholder="Ежемесячно до 10 числа, 15000 ₽"
                                />
                                <InputError message={errors.payment_terms} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="termination_terms">
                                    Условия расторжения *
                                </Label>
                                <textarea
                                    id="termination_terms"
                                    name="termination_terms"
                                    rows={3}
                                    required
                                    className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                    defaultValue={
                                        contract?.termination_terms ?? ''
                                    }
                                    placeholder="Уведомление за 30 дней"
                                />
                                <InputError message={errors.termination_terms} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="file">
                                    Файл договора{' '}
                                    {isEdit ? '(заменить)' : '*'}
                                </Label>
                                <Input
                                    id="file"
                                    name="file"
                                    type="file"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    required={!isEdit}
                                />
                                {isEdit && contract?.file_url && (
                                    <a
                                        href={contract.file_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-sm text-primary underline"
                                    >
                                        Текущий файл
                                    </a>
                                )}
                                <InputError message={errors.file} />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button disabled={processing} type="submit">
                                    {isEdit ? 'Сохранить' : 'Добавить'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
