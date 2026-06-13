import { Link, usePage, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import amenities from '@/routes/amenities';
import contracts from '@/routes/contracts';
import rooms from '@/routes/rooms';
import type { User } from '@/types/auth';
import type { Rooms } from '@/types/rooms/rooms';

type PageProps = {
    room: Rooms;
    renters: User[];
    hasAmenities: boolean;
    hasContracts: boolean;
};

export default function RoomsAddRenterPage() {
    const page = usePage<PageProps>();
    const { room, renters, hasAmenities, hasContracts } = page.props;
    const canAssign = hasAmenities && hasContracts;

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
                <>
                    {!canAssign && (
                        <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <p className="mb-2 font-medium">
                                Нельзя назначить арендатора: сначала добавьте
                                услугу и договор.
                            </p>
                            <div className="flex flex-wrap gap-3">
                                {!hasAmenities && (
                                    <Link
                                        href={amenities.get(room.id)}
                                        className="underline"
                                    >
                                        Добавить услугу
                                    </Link>
                                )}
                                {!hasContracts && (
                                    <Link
                                        href={contracts.adminGet(room.id)}
                                        className="underline"
                                    >
                                        Добавить договор
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
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
                                        onClick={() =>
                                            handleSelectRenter(renter)
                                        }
                                        size="sm"
                                        disabled={!canAssign}
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
                </>
            )}
        </>
    );
}
