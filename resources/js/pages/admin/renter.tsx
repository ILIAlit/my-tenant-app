import { Link, Head, usePage } from '@inertiajs/react';
import { Edit, Eye, Plus, Settings, Trash2 } from 'lucide-react';
import { useState } from 'react';
import RenterFormDialog from '@/components/renters/renter-form-dialog';
import RenterViewDialog from '@/components/renters/renter-view-dialog';
import { Button } from '@/components/ui/button';
import { useOpenCreateFromQuery } from '@/hooks/use-open-create-from-query';
import renters from '@/routes/renters';
import type { User } from '@/types';
import { roomWithFloorLabel } from '@/types';
import { renterFullName } from '@/utils/renter';

type PageProps = {
    renters: User[];
};

export default function RentersPage() {
    const { renters: rentersList } = usePage<PageProps>().props;
    const [formOpen, setFormOpen] = useState(false);
    const [viewOpen, setViewOpen] = useState(false);
    const [selectedRenter, setSelectedRenter] = useState<User | null>(null);

    const openCreate = () => {
        setSelectedRenter(null);
        setFormOpen(true);
    };

    useOpenCreateFromQuery(openCreate);

    const openEdit = (renter: User) => {
        setSelectedRenter(renter);
        setFormOpen(true);
    };

    const openView = (renter: User) => {
        setSelectedRenter(renter);
        setViewOpen(true);
    };

    const formatRoom = (renter: User): string => {
        if (!renter.room) {
            return '—';
        }

        return roomWithFloorLabel(renter.room);
    };

    return (
        <>
            <Head title="Арендаторы" />

            <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 className="mb-2 text-2xl font-semibold">
                        Арендаторы
                    </h1>
                    <p className="text-gray-500">
                        Управление арендаторами и контактами
                    </p>
                </div>
                <Button onClick={openCreate}>
                    <Plus size={20} />
                    Добавить арендатора
                </Button>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Список арендаторов</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    ФИО
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Логин
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Телефон
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Комната
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Почта
                                </th>
                                <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {rentersList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Арендаторов пока нет
                                    </td>
                                </tr>
                            ) : (
                                rentersList.map((renter) => (
                                    <tr
                                        key={renter.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            <Link
                                                href={renters.settings(renter.id)}
                                                className="hover:underline"
                                            >
                                                {renterFullName(renter)}
                                            </Link>
                                        </td>
                                        <td className="px-6 py-4">
                                            {renter.login}
                                        </td>
                                        <td className="px-6 py-4">
                                            {renter.phone || '—'}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatRoom(renter)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {renter.email}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={renters.settings(
                                                            renter.id,
                                                        )}
                                                        aria-label="Настройка"
                                                    >
                                                        <Settings
                                                            size={18}
                                                            className="text-gray-600"
                                                        />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        openView(renter)
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
                                                        openEdit(renter)
                                                    }
                                                    aria-label="Редактировать"
                                                >
                                                    <Edit
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </Button>
                                                <Link
                                                    href={renters.delete(
                                                        renter.id,
                                                    )}
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

            <RenterFormDialog
                renter={selectedRenter}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <RenterViewDialog
                renter={selectedRenter}
                open={viewOpen}
                onOpenChange={setViewOpen}
            />
        </>
    );
}

RentersPage.layout = {
    breadcrumbs: [
        {
            title: 'Арендаторы',
            href: renters.get(),
        },
    ],
};
