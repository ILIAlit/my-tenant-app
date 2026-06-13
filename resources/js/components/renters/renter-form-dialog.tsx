import { Form } from '@inertiajs/react';
import AdminRenterController from '@/actions/App/Http/Controllers/Admin/Renter/AdminRenterController';
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
import type { User } from '@/types';

type RenterFormDialogProps = {
    renter: User;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RenterFormDialog({
    renter,
    open,
    onOpenChange,
}: RenterFormDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Редактировать арендатора</DialogTitle>
                </DialogHeader>

                <Form
                    key={renter.id}
                    {...AdminRenterController.updateRenters.form(renter.id)}
                    options={{ preserveScroll: true }}
                    className="space-y-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="last_name">Фамилия</Label>
                                    <Input
                                        id="last_name"
                                        name="last_name"
                                        type="text"
                                        defaultValue={renter.last_name ?? ''}
                                    />
                                    <InputError message={errors.last_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="name">Имя *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        defaultValue={renter.name ?? ''}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="middle_name">Отчество</Label>
                                    <Input
                                        id="middle_name"
                                        name="middle_name"
                                        type="text"
                                        defaultValue={renter.middle_name ?? ''}
                                    />
                                    <InputError message={errors.middle_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Телефон</Label>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        type="text"
                                        defaultValue={renter.phone ?? ''}
                                    />
                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Почта *</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    defaultValue={renter.email ?? ''}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="login">Логин *</Label>
                                <Input
                                    id="login"
                                    name="login"
                                    type="text"
                                    required
                                    defaultValue={renter.login ?? ''}
                                />
                                <InputError message={errors.login} />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button disabled={processing} type="submit">
                                    Сохранить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
