import { usePage, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import AdminEditRoomsController from '@/actions/App/Http/Controllers/Admin/Rooms/AdminEditRoomsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PageHeader from '@/components/ui/page-header';
import rooms from '@/routes/rooms';
import type { User } from '@/types/auth';
import type { Rooms } from '@/types/rooms/rooms';

type PageProps = {
    room: Rooms;
    renters: User[];
};

export default function RoomsUpdatePage() {
    const page = usePage<PageProps>();
    const { room: roomItem } = page.props;

    return (
        <>
            <PageHeader
                title="Редактирование комнаты"
                description="Обновите информацию о комнате"
            />
            <Form
                {...AdminEditRoomsController.updateRooms.form(roomItem.id)}
                options={{
                    preserveScroll: true,
                }}
                className="space-y-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label>Номер комнаты *</Label>

                            <Input
                                id="number"
                                className="mt-1 block w-full"
                                name="number"
                                type="number"
                                defaultValue={roomItem.number}
                                required
                            />

                            <InputError
                                className="mt-2"
                                message={errors.number}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="floor">Этаж *</Label>

                            <Input
                                id="floor"
                                type="number"
                                className="mt-1 block w-full"
                                name="floor"
                                defaultValue={roomItem.floor}
                                required
                                min="0"
                            />

                            <InputError
                                className="mt-2"
                                message={errors.floor}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="square">Площадь (м²) *</Label>

                            <Input
                                id="square"
                                type="number"
                                step="0.1"
                                className="mt-1 block w-full"
                                name="square"
                                defaultValue={roomItem.square}
                                required
                                min="0.1"
                            />

                            <InputError
                                className="mt-2"
                                message={errors.square}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="status" id="status-label">
                                Статус *
                            </Label>

                            <select
                                id="status"
                                name="status"
                                className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"
                                required
                                defaultValue={roomItem.status ?? 'free'}
                                aria-labelledby="status-label"
                                title="Статус"
                            >
                                <option value="free">Свободна</option>
                                <option value="used">Занята</option>
                                <option value="repair">Ремонт</option>
                            </select>

                            <InputError
                                className="mt-2"
                                message={errors.status}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="date_of_last_repair">
                                Дата последнего ремонта
                            </Label>

                            <Input
                                id="date_of_last_repair"
                                type="date"
                                className="mt-1 block w-full"
                                name="date_of_last_repair"
                                defaultValue={
                                    roomItem.date_of_last_repair
                                        ? roomItem.date_of_last_repair.split(
                                              ' ',
                                          )[0]
                                        : ''
                                }
                            />

                            <InputError
                                className="mt-2"
                                message={errors.date_of_last_repair}
                            />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="notes">Примечания</Label>

                            <textarea
                                id="notes"
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
                                name="notes"
                                rows={3}
                                defaultValue={roomItem.notes || ''}
                                placeholder="Дополнительная информация"
                            />

                            <InputError
                                className="mt-2"
                                message={errors.notes}
                            />
                        </div>

                        <Link
                            type="button"
                            className="btn btn-outline mr-5"
                            href={rooms.get()}
                        >
                            Закрыть
                        </Link>

                        <Button disabled={processing} type="submit">
                            Сохранить
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}
