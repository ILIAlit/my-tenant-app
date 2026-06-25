import { Head, Link, usePage } from '@inertiajs/react';
import { Edit, Eye, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import RoomFormDialog from '@/components/rooms/room-form-dialog';
import RoomViewDialog from '@/components/rooms/room-view-dialog';
import { Button } from '@/components/ui/button';
import { useOpenCreateFromQuery } from '@/hooks/use-open-create-from-query';
import { cn } from '@/lib/utils';
import rooms from '@/routes/rooms';
import type { Room, RoomStatus } from '@/types';
import { roomStatusLabels, roomTypeLabels, formatRoomFloor } from '@/types';

type PageProps = {
    rooms: Room[];
};

const statusBadge: Record<RoomStatus, string> = {
    free: 'bg-green-100 text-green-800',
    repair: 'bg-amber-100 text-amber-800',
    occupied: 'bg-blue-100 text-blue-800',
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '—';

export default function RoomsPage() {
    const { rooms: roomsList } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [viewOpen, setViewOpen] = useState(false);
    const [selectedRoom, setSelectedRoom] = useState<Room | null>(null);

    const openCreate = () => {
        setSelectedRoom(null);
        setFormOpen(true);
    };

    useOpenCreateFromQuery(openCreate);

    const openEdit = (room: Room) => {
        setSelectedRoom(room);
        setFormOpen(true);
    };

    const openView = (room: Room) => {
        setSelectedRoom(room);
        setViewOpen(true);
    };

    return (
        <>
            <Head title="Комнаты и гаражи" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">
                        Комнаты и гаражи
                    </h1>
                    <p className="text-gray-500">
                        Управление комнатами, гаражами, этажами и статусами
                    </p>
                </div>
                <Button onClick={openCreate}>
                    <Plus size={20} />
                    Добавить помещение
                </Button>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Список помещений</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Тип
                                </th>
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
                            {roomsList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Помещений пока нет
                                    </td>
                                </tr>
                            ) : (
                                roomsList.map((room) => (
                                    <tr
                                        key={room.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4">
                                            {roomTypeLabels[room.type]}
                                        </td>
                                        <td className="px-6 py-4 font-medium">
                                            {room.number}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatRoomFloor(room.floor)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {room.area} м²
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={cn(
                                                    'inline-block rounded-full px-3 py-1 text-sm font-medium',
                                                    statusBadge[room.status],
                                                )}
                                            >
                                                {
                                                    roomStatusLabels[
                                                        room.status
                                                    ]
                                                }
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatDate(room.last_repair_date)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        openView(room)
                                                    }
                                                    aria-label="Просмотр"
                                                >
                                                    <Eye
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        openEdit(room)
                                                    }
                                                    aria-label="Редактировать"
                                                >
                                                    <Edit
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </Button>
                                                <Link
                                                    href={rooms.delete(room.id)}
                                                    as="button"
                                                    method="delete"
                                                    preserveScroll
                                                    className="inline-flex size-9 items-center justify-center rounded-md hover:bg-red-50"
                                                    aria-label="Удалить"
                                                >
                                                    <Trash2
                                                        size={18}
                                                        className="text-red-600"
                                                    />
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <RoomFormDialog
                room={selectedRoom}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <RoomViewDialog
                room={selectedRoom}
                open={viewOpen}
                onOpenChange={setViewOpen}
            />
        </>
    );
}

RoomsPage.layout = {
    breadcrumbs: [
        {
            title: 'Комнаты и гаражи',
            href: rooms.get(),
        },
    ],
};
