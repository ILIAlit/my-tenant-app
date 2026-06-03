import { Link, usePage } from '@inertiajs/react';
import { Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';
import UpdateRoomForm from '@/components/rooms/update-room-form';
import { Button } from '@/components/ui/button';
import { Role } from '@/enum/auth';
import rooms from '@/routes/rooms';
import type { Rooms as RoomType, User } from '@/types';

type PageProps = {
    auth: User;
};

export default function RoomsList({ roomItems }: { roomItems: RoomType[] }) {
    const { user } = usePage<PageProps>().props.auth;
    const [selectedRoom, setSelectedRoom] = useState<RoomType | null>(null);
    const [isUpdateModalOpen, setIsUpdateModalOpen] = useState(false);

    const handleEditClick = (room: RoomType) => {
        setSelectedRoom(room);
        setIsUpdateModalOpen(true);
    };

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
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">
                        Список комнат ({roomItems.length})
                    </h3>
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
                                <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {roomItems.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        Нет добавленных комнат
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
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                {user.role === Role.Admin && (
                                                    <>
                                                        <Button
                                                            onClick={() =>
                                                                handleEditClick(
                                                                    room,
                                                                )
                                                            }
                                                            className="flex items-center justify-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            <Edit size={16} />
                                                            Изменить
                                                        </Button>
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
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
            {selectedRoom && (
                <UpdateRoomForm
                    room={selectedRoom}
                    open={isUpdateModalOpen}
                    onOpenChange={setIsUpdateModalOpen}
                />
            )}
        </>
    );
}
