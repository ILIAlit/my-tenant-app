import { Form } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import rooms from '@/routes/rooms';
import type { Room, RoomType } from '@/types';
import { roomStatusOptions, roomTypeLabels, roomTypeOptions } from '@/types';

type Props = {
    room?: Room | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RoomFormDialog({
    room = null,
    open,
    onOpenChange,
}: Props) {
    const isEditing = room !== null;
    const [selectedType, setSelectedType] = useState<RoomType>(
        room?.type ?? 'room',
    );
    const showFloor = selectedType === 'room';

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing
                            ? `Редактировать ${roomTypeLabels[room.type].toLowerCase()}`
                            : 'Добавить помещение'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    action={
                        isEditing
                            ? rooms.update.url(room.id)
                            : rooms.create.url()
                    }
                    method={isEditing ? 'put' : 'post'}
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess={!isEditing}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="type">Тип *</Label>
                                <select
                                    id="type"
                                    name="type"
                                    defaultValue={room?.type ?? 'room'}
                                    required
                                    disabled={isEditing}
                                    onChange={(event) =>
                                        setSelectedType(
                                            event.target.value as RoomType,
                                        )
                                    }
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {roomTypeOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.type} />
                            </div>

                            <div
                                className={
                                    showFloor
                                        ? 'grid gap-4 sm:grid-cols-2'
                                        : 'grid gap-2'
                                }
                            >
                                <div className="grid gap-2">
                                    <Label htmlFor="number">Номер *</Label>
                                    <Input
                                        id="number"
                                        name="number"
                                        defaultValue={room?.number ?? ''}
                                        required
                                        placeholder="12, 12А, G-1"
                                    />
                                    <InputError message={errors.number} />
                                </div>
                                {showFloor && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="floor">Этаж *</Label>
                                        <Input
                                            id="floor"
                                            name="floor"
                                            type="number"
                                            min={0}
                                            defaultValue={room?.floor ?? ''}
                                            required
                                            placeholder="1"
                                        />
                                        <InputError message={errors.floor} />
                                    </div>
                                )}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="area">Площадь (м²) *</Label>
                                    <Input
                                        id="area"
                                        name="area"
                                        type="number"
                                        min={0.01}
                                        step={0.01}
                                        defaultValue={room?.area ?? ''}
                                        required
                                        placeholder="45.5"
                                    />
                                    <InputError message={errors.area} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Статус *</Label>
                                    <select
                                        id="status"
                                        name="status"
                                        defaultValue={room?.status ?? 'free'}
                                        required
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    >
                                        {roomStatusOptions.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="last_repair_date">
                                    Дата последнего ремонта
                                </Label>
                                <Input
                                    id="last_repair_date"
                                    name="last_repair_date"
                                    type="date"
                                    defaultValue={room?.last_repair_date ?? ''}
                                />
                                <InputError
                                    message={errors.last_repair_date}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Примечания</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={room?.notes ?? ''}
                                    placeholder="Дополнительная информация"
                                    className="block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {isEditing ? 'Сохранить' : 'Создать'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
