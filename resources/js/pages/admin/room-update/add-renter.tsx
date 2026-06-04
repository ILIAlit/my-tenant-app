import { usePage, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import rooms from '@/routes/rooms';
import type { User } from '@/types/auth';
import type { Rooms } from '@/types/rooms/rooms';

type PageProps = {
    room: Rooms;
    renters: User[];
};

export default function RoomsAddRenterPage() {
    const page = usePage<PageProps>();
    const { room, renters } = page.props;

    const { put } = useForm();

    const handleSelectRenter = (renter: User) => {
        put(rooms.addRenter({ room_id: room.id, renter_id: renter.id }).url, {
            preserveScroll: true,
        });
    };

    const handleDeleteRenter = () => {
        put(rooms.deleteRenter(room.id).url, {
            preserveScroll: true,
        });
    };

    return (
        <>
            <PageHeader
                title="Добавить арендатора"
                description="Выберите арендатора для добавления к комнате."
            />
            {room.user_id ? (
                <div className="mb-6 rounded-lg border bg-yellow-50 p-4">
                    <p className="mb-4 text-sm text-yellow-700">
                        Эта комната уже имеет назначенного арендатора.
                    </p>
                    <Button
                        onClick={handleDeleteRenter}
                        size="sm"
                        variant="destructive"
                    >
                        Удалить текущего арендатора
                    </Button>
                </div>
            ) : (
                <div className="space-y-2">
                    {renters && renters.length > 0 ? (
                        renters.map((renter) => (
                            <div
                                key={renter.id}
                                className="flex items-center justify-between rounded-lg border p-3 hover:bg-gray-50"
                            >
                                <div>
                                    <p className="font-medium">
                                        {renter.last_name} {renter.name}{' '}
                                        {renter.middle_name}
                                    </p>
                                    <p className="text-sm text-gray-500">
                                        {renter.email}
                                    </p>
                                </div>
                                <Button
                                    onClick={() => handleSelectRenter(renter)}
                                    size="sm"
                                >
                                    Выбрать
                                </Button>
                            </div>
                        ))
                    ) : (
                        <p className="py-8 text-center text-gray-500">
                            Нет доступных арендаторов
                        </p>
                    )}
                </div>
            )}
        </>
    );
}
