import { Form } from '@inertiajs/react';
import AdminRoomsController from '@/actions/App/Http/Controllers/Admin/Rooms/AdminRoomsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Rooms } from '@/types/rooms';

type UpdateRoomFormProps = {
    room: Rooms;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function UpdateRoomForm({
    room,
    open,
    onOpenChange,
}: UpdateRoomFormProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        Редактировать комнату №{room.number}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    {...AdminRoomsController.updateRooms.form(room.id)}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
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
                                    defaultValue={room.number}
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
                                    defaultValue={room.floor}
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
                                    defaultValue={room.square}
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
                                    defaultValue={room.status ?? 'free'}
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
                                        room.date_of_last_repair
                                            ? room.date_of_last_repair.split(
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
                                    defaultValue={room.notes || ''}
                                    placeholder="Дополнительная информация"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.notes}
                                />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Закрыть
                                    </Button>
                                </DialogClose>
                                <Button disabled={processing} type="submit">
                                    Сохранить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
