import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import renters from '@/routes/renters';
import type { User } from '@/types';

type Props = {
    renter: User;
    onCancel: () => void;
    onSuccess: () => void;
};

export default function RenterProfileForm({
    renter,
    onCancel,
    onSuccess,
}: Props) {
    return (
        <Form
            action={renters.update.url(renter.id)}
            method="put"
            options={{ preserveScroll: true }}
            className="space-y-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/50 p-4"
            onSuccess={onSuccess}
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="last_name">Фамилия *</Label>
                            <Input
                                id="last_name"
                                name="last_name"
                                defaultValue={renter.last_name ?? ''}
                                required
                            />
                            <InputError message={errors.last_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name">Имя *</Label>
                            <Input
                                id="name"
                                name="name"
                                defaultValue={renter.name ?? ''}
                                required
                            />
                            <InputError message={errors.name} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="middle_name">Отчество</Label>
                        <Input
                            id="middle_name"
                            name="middle_name"
                            defaultValue={renter.middle_name ?? ''}
                        />
                        <InputError message={errors.middle_name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="login">Логин *</Label>
                        <Input
                            id="login"
                            name="login"
                            defaultValue={renter.login}
                            required
                        />
                        <InputError message={errors.login} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Почта *</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            defaultValue={renter.email}
                            required
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone">Телефон</Label>
                        <Input
                            id="phone"
                            name="phone"
                            defaultValue={renter.phone ?? ''}
                        />
                        <InputError message={errors.phone} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Новый пароль</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            autoComplete="new-password"
                            placeholder="Оставьте пустым, чтобы не менять"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Подтверждение пароля
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autoComplete="new-password"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            Сохранить
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onCancel}
                        >
                            Отмена
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
