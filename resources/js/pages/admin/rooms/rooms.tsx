import { Head, usePage } from '@inertiajs/react';
import CreateRoomForm from '@/components/rooms/create-room-form';
import RoomsList from '@/components/rooms/rooms-list';
import PageHeader from '@/components/ui/page-header';
import { Role } from '@/enum/auth';
import rooms from '@/routes/rooms';
import type { Rooms as RoomType } from '@/types';
import type { Auth } from '@/types/auth';

type PageProps = {
    rooms: RoomType[];
    auth: Auth;
};

export default function RoomsPage() {
    const page = usePage<PageProps>();
    const { user } = page.props.auth;

    return (
        <>
            <Head title="Комнаты" />
            <PageHeader
                title="Комнаты"
                description="Управление комнатами, статусом и информацией о ремонте"
            />
            {user.role === Role.Admin && <CreateRoomForm />}

            <RoomsList />
        </>
    );
}

RoomsPage.layout = {
    breadcrumbs: [
        {
            title: 'Комнаты',
            href: rooms.get(),
        },
    ],
};
