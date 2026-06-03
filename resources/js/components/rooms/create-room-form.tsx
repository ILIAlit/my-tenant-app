import { Form } from '@inertiajs/react';
import { useState } from 'react';
import AdminRoomsController from '@/actions/App/Http/Controllers/Admin/Rooms/AdminRoomsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function CreateRoomForm() {
    const [hidden, setHidden] = useState(true);

    return hidden ? (
        <div className="mt-4 mb-4 rounded-xl border border-gray-200 bg-white p-6">
            <div className="flex items-center justify-between">
                <Button onClick={() => setHidden(false)} className="">
                    Создать комнату
                </Button>
            </div>
        </div>
    ) : (
        <div hidden={hidden} className="mt-4 mb-6 max-w-2xl">
            <Form
                {...AdminRoomsController.createRooms.form()}
                options={{
                    preserveScroll: true,
                }}
                className="space-y-6 rounded-xl border border-gray-200 bg-white p-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="number">Номер комнаты *</Label>

                                <Input
                                    id="number"
                                    className="mt-1 block w-full"
                                    name="number"
                                    type="number"
                                    required
                                    placeholder="101"
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
                                    required
                                    min="0"
                                    placeholder="1"
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
                                    required
                                    min="0.1"
                                    placeholder="45.5"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.square}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Статус *</Label>

                                <select
                                    id="status"
                                    name="status"
                                    className="mt-1 block w-full rounded-md border-gray-300 px-3 py-2"
                                    required
                                    defaultValue="free"
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
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.date_of_last_repair}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Примечания</Label>

                                <textarea
                                    id="notes"
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
                                    name="notes"
                                    rows={3}
                                    placeholder="Дополнительная информация"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.notes}
                                />
                            </div>
                        </div>

                        <div className="flex items-center gap-4">
                            <Button
                                disabled={processing}
                                data-test="create-room-button"
                            >
                                Создать
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setHidden(true)}
                            >
                                Закрыть
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
