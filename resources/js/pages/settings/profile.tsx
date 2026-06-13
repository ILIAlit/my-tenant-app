import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Настройка профиля" />

            <h1 className="sr-only">Настройка профиля</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Профиль"
                    description="Обновить данные профиля"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Логин</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.login}
                                    name="login"
                                    required
                                    autoComplete="login"
                                    placeholder="логин"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.login}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Почта</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="почта"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Ваш email не подтверждён.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Нажмите, чтобы отправить письмо
                                                повторно.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                Новая ссылка для подтверждения
                                                отправлена на ваш email.
                                            </div>
                                        )}
                                    </div>
                                )}

                            <div className="grid gap-2">
                                <Label htmlFor="email">Номер телефона</Label>

                                <Input
                                    id="phone"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.phone}
                                    name="phone"
                                    autoComplete="userphone"
                                    placeholder="+375255488776"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.phone}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Имя</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    autoComplete="username"
                                    placeholder="имя"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Фамилия</Label>

                                <Input
                                    id="last_name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.last_name}
                                    name="last_name"
                                    autoComplete="username"
                                    placeholder="фамилия"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.last_name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Отчество</Label>

                                <Input
                                    id="middle_name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.middle_name}
                                    name="middle_name"
                                    autoComplete="username"
                                    placeholder="отчество"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.middle_name}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Сохранить
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Профиль',
            href: edit(),
        },
    ],
};
