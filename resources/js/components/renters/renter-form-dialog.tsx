import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
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
import renters from '@/routes/renters';
import type { User } from '@/types';

type Props = {
    renter?: User | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RenterFormDialog({
    renter = null,
    open,
    onOpenChange,
}: Props) {
    const isEditing = renter !== null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing
                            ? 'Редактировать арендатора'
                            : 'Добавить арендатора'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    action={
                        isEditing
                            ? renters.update.url(renter.id)
                            : renters.create.url()
                    }
                    method={isEditing ? 'put' : 'post'}
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess={!isEditing}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="last_name">Фамилия *</Label>
                                    <Input
                                        id="last_name"
                                        name="last_name"
                                        defaultValue={renter?.last_name ?? ''}
                                        required
                                        placeholder="Иванов"
                                    />
                                    <InputError message={errors.last_name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Имя *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={renter?.name ?? ''}
                                        required
                                        placeholder="Иван"
                                    />
                                    <InputError message={errors.name} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="middle_name">Отчество</Label>
                                <Input
                                    id="middle_name"
                                    name="middle_name"
                                    defaultValue={renter?.middle_name ?? ''}
                                    placeholder="Иванович"
                                />
                                <InputError message={errors.middle_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="login">Логин *</Label>
                                <Input
                                    id="login"
                                    name="login"
                                    defaultValue={renter?.login ?? ''}
                                    required
                                    placeholder="ivanov"
                                />
                                <InputError message={errors.login} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Почта *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    defaultValue={renter?.email ?? ''}
                                    required
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Телефон</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    defaultValue={renter?.phone ?? ''}
                                    placeholder="+375291234567"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {isEditing
                                        ? 'Новый пароль'
                                        : 'Пароль *'}
                                </Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required={!isEditing}
                                    autoComplete="new-password"
                                    placeholder={
                                        isEditing
                                            ? 'Оставьте пустым, чтобы не менять'
                                            : 'Пароль'
                                    }
                                />
                                <InputError message={errors.password} />
                            </div>

                            {!isEditing && (
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Подтверждение пароля *
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        required
                                        autoComplete="new-password"
                                        placeholder="Повторите пароль"
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                            )}

                            {isEditing && (
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Подтверждение пароля
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        placeholder="Повторите новый пароль"
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                            )}

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
