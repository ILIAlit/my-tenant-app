import { usePage } from '@inertiajs/react';
import RoomsList from '@/components/rooms/rooms-list';
import PageHeader from '@/components/ui/page-header';
import rooms from '@/routes/rooms';

export default function RenterRoomsPage() {
    const page = usePage();
    console.log(page);

    return (
        <>
            <PageHeader
                title="Мои комнаты"
                description="Список ваших арендованных комнат"
            />
            <RoomsList />
        </>
    );
}

RenterRoomsPage.layout = {
    breadcrumbs: [
        {
            title: 'Комнаты',
            href: rooms.getRenterRooms(),
        },
    ],
};
