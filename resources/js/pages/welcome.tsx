import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Добро пожаловать" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#1b1b18]">
                <header className="absolute top-0 w-full max-w-4xl px-6 py-6">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a]"
                            >
                                Главная
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035]"
                                >
                                    Вход
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a]"
                                >
                                    Регистрация
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <main className="flex w-full max-w-lg flex-col items-center text-center">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Управление арендой
                    </h1>
                    <p className="mt-3 text-[#706f6c]">
                        Начисления, платежи, договоры и показания счётчиков — в
                        одном личном кабинете.
                    </p>

                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm leading-normal text-white hover:bg-black"
                            >
                                Перейти в кабинет
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm leading-normal text-white hover:bg-black"
                                >
                                    Войти
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-2 text-sm leading-normal hover:border-[#1915014a]"
                                >
                                    Создать аккаунт
                                </Link>
                            </>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
