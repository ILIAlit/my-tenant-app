import { Link, usePage, router } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import AdminAmenitiesController from '@/actions/App/Http/Controllers/Admin/Amenities/AdminAmenitiesController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PageHeader from '@/components/ui/page-header';
import amenities from '@/routes/amenities';
import type { Amenities, Rooms } from '@/types';

type PageProps = {
    room: Rooms;
    amenities: Amenities[];
};

export default function RoomAddAmenitiesPage() {
    const page = usePage<PageProps>();
    const roomItem = page.props.room;
    const amenitiesItems = page.props.amenities || [];
    const [isFormHidden, setIsFormHidden] = useState(true);

    const handleDelete = (id: number) => {};

    return (
        <>
            <PageHeader
                title="Управление услугами"
                description="Создайте или удалите услуги"
            />
            <div
                hidden={!isFormHidden}
                className="mt-4 mb-4 rounded-xl border border-gray-200 bg-white p-6"
            >
                <div className="flex items-center justify-between">
                    <Button onClick={() => setIsFormHidden(false)} className="">
                        Создать услугу
                    </Button>
                </div>
            </div>
            <Form
                hidden={isFormHidden}
                {...AdminAmenitiesController.create.form()}
                options={{
                    preserveScroll: true,
                }}
                className="space-y-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Input
                                id="rooms_id"
                                className="mt-1 block w-full"
                                name="rooms_id"
                                defaultValue={roomItem.id}
                                required
                                hidden
                            />

                            <InputError
                                className="mt-2"
                                message={errors.number}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="floor">Название</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                name="name"
                                required
                                min="0"
                            />

                            <InputError
                                className="mt-2"
                                message={errors.name}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="square">Стоимость</Label>

                            <Input
                                id="price"
                                type="number"
                                className="mt-1 block w-full"
                                name="price"
                                required
                            />

                            <InputError
                                className="mt-2"
                                message={errors.price}
                            />
                        </div>
                        <div className="flex items-center gap-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsFormHidden(true)}
                            >
                                Закрыть
                            </Button>

                            <Button disabled={processing} type="submit">
                                Создать
                            </Button>
                        </div>
                    </>
                )}
            </Form>
            {amenitiesItems.length > 0 && (
                <div className="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                        Список услуг ({amenitiesItems.length})
                    </h2>
                    <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        {amenitiesItems.map((amenityItem) => (
                            <div
                                key={amenityItem.id}
                                className="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-slate-600 dark:bg-slate-800"
                            >
                                <div className="flex-1">
                                    <p className="font-medium text-slate-900 dark:text-white">
                                        {amenityItem.name}
                                    </p>
                                    <p className="text-sm text-slate-600 dark:text-slate-400">
                                        {amenityItem.price} ₽
                                    </p>
                                </div>
                                <Link
                                    href={amenities.delete([
                                        amenityItem.id,
                                        roomItem.id,
                                    ])}
                                    type="button"
                                    onClick={() => handleDelete(amenityItem.id)}
                                    className="ml-2 inline-flex items-center justify-center rounded-md p-2 text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                    title="Удалить услугу"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Link>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </>
    );
}
