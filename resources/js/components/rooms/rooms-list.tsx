import { Link, router, usePage } from '@inertiajs/react';
import { Edit, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Role } from '@/enum/auth';
import rooms from '@/routes/rooms';
import type { Rooms as RoomType } from '@/types';
import type { Auth } from '@/types/auth';

type RoomFilters = {
    search: string;
    status: string;
};

type PageProps = {
    auth: Auth;
    rooms: RoomType[];
    filters?: RoomFilters;
};

const statusOptions = [
    { value: '', label: 'Все статусы' },
    { value: 'free', label: 'Свободна' },
    { value: 'used', label: 'Занята' },
    { value: 'repair', label: 'Ремонт' },
];

export default function RoomsList() {
    const { auth, rooms: roomItems, filters } = usePage<PageProps>().props;
    const user = auth.user;
    const isAdmin = user.role === Role.Admin;

    const [search, setSearch] = useState(filters?.search ?? '');
    const [status, setStatus] = useState(filters?.status ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (!isAdmin) {
            return;
        }

        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            const query: Record<string, string> = {};

            if (search.trim()) {
                query.search = search.trim();
            }

            if (status) {
                query.status = status;
            }

            router.get(rooms.get().url, query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [search, status, isAdmin]);

    const getStatusBadge = (status: string) => {
        const statusMap: Record<
            string,
            { bg: string; text: string; label: string }
        > = {
            free: {
                bg: 'bg-green-100',
                text: 'text-green-800',
                label: 'Свободна',
            },
            used: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Занята' },
            repair: {
                bg: 'bg-yellow-100',
                text: 'text-yellow-800',
                label: 'Ремонт',
            },
        };
        const style = statusMap[status] || statusMap.free;

        return (
            <span
                className={`inline-block rounded-full px-3 py-1 text-sm font-medium ${style.bg} ${style.text}`}
            >
                {style.label}
            </span>
        );
    };

    return (
        <>
            {' '}
            <div className="mt-6 rounded-xl border border-gray-200 bg-white">
                <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <h3 className="font-medium">
                        Список комнат ({roomItems.length})
                    </h3>
                    {isAdmin && (
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="relative">
                                <Search
                                    size={16}
                                    className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                                />
                                <Input
                                    type="search"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Поиск по номеру"
                                    className="pl-9 sm:w-56"
                                />
                            </div>
                            <select
                                value={status}
                                onChange={(event) =>
                                    setStatus(event.target.value)
                                }
                                className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
                            >
                                {statusOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Номер
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Этаж
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Площадь
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Статус
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Последний ремонт
                                </th>
                                {user.role === Role.Admin && (
                                    <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                        Действия
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {!roomItems.length ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        {search.trim() || status
                                            ? 'Комнаты не найдены'
                                            : isAdmin
                                              ? 'Нет добавленных комнат'
                                              : 'У вас пока нет арендованных комнат'}
                                    </td>
                                </tr>
                            ) : (
                                roomItems.map((room) => (
                                    <tr
                                        key={room.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            №{room.number}
                                        </td>
                                        <td className="px-6 py-4">
                                            {room.floor}
                                        </td>
                                        <td className="px-6 py-4">
                                            {room.square} м²
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusBadge(room.status)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {room.date_of_last_repair
                                                ? new Date(
                                                      room.date_of_last_repair,
                                                  ).toLocaleDateString('ru-RU')
                                                : '—'}
                                        </td>
                                        {user.role === Role.Admin && (
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    <>
                                                        <Link
                                                            href={rooms.getUpdate(
                                                                room.id,
                                                            )}
                                                            as="button"
                                                            method="get"
                                                            className="flex items-center justify-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            <Edit size={16} />
                                                            Изменить
                                                        </Link>
                                                        <Link
                                                            href={rooms.delete(
                                                                room.id,
                                                            )}
                                                            as="button"
                                                            method="delete"
                                                            className="rounded-lg bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
                                                        >
                                                            <Trash2 size={16} />
                                                        </Link>
                                                    </>
                                                </div>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
