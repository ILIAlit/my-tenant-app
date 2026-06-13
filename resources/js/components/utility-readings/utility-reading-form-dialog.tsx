import { Form } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import UtilityReadingsController from '@/actions/App/Http/Controllers/UtilityReadings/UtilityReadingsController';
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
import type { RoomUtilityData, UtilityReadingPeriod } from '@/types';

type UtilityReadingFormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    roomsUtilityData: RoomUtilityData[];
};

export default function UtilityReadingFormDialog({
    open,
    onOpenChange,
    roomsUtilityData,
}: UtilityReadingFormDialogProps) {
    const roomsWithPeriods = useMemo(
        () =>
            roomsUtilityData.filter(
                (room) => room.availablePeriods.length > 0,
            ),
        [roomsUtilityData],
    );

    const [roomId, setRoomId] = useState<string>('');
    const [periodStart, setPeriodStart] = useState<string>('');

    const availablePeriods: UtilityReadingPeriod[] = useMemo(() => {
        const room = roomsWithPeriods.find(
            (item) => item.room_id === Number(roomId),
        );

        return room?.availablePeriods ?? [];
    }, [roomId, roomsWithPeriods]);

    const selectedPeriod = availablePeriods.find(
        (period) => period.period_start === periodStart,
    );

    useEffect(() => {
        if (!open) {
            setRoomId('');
            setPeriodStart('');

            return;
        }

        if (roomsWithPeriods.length === 1 && !roomId) {
            setRoomId(String(roomsWithPeriods[0].room_id));
        }
    }, [open, roomId, roomsWithPeriods]);

    useEffect(() => {
        if (availablePeriods.length === 1) {
            setPeriodStart(availablePeriods[0].period_start);
        } else if (
            periodStart &&
            !availablePeriods.some((period) => period.period_start === periodStart)
        ) {
            setPeriodStart('');
        }
    }, [availablePeriods, periodStart]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Внести показания</DialogTitle>
                </DialogHeader>

                {roomsWithPeriods.length === 0 ? (
                    <p className="text-sm text-gray-500">
                        Нет доступных периодов для внесения показаний.
                    </p>
                ) : (
                    <Form
                        key={`${roomId}-${periodStart}`}
                        {...UtilityReadingsController.create.form()}
                        encType="multipart/form-data"
                        options={{ preserveScroll: true }}
                        className="space-y-5"
                        onSuccess={() => onOpenChange(false)}
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="rooms_id">Комната *</Label>
                                    <select
                                        id="rooms_id"
                                        name="rooms_id"
                                        className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                        required
                                        value={roomId}
                                        onChange={(event) => {
                                            setRoomId(event.target.value);
                                            setPeriodStart('');
                                        }}
                                    >
                                        <option value="" disabled>
                                            Выберите комнату
                                        </option>
                                        {roomsWithPeriods.map((room) => (
                                            <option
                                                key={room.room_id}
                                                value={room.room_id}
                                            >
                                                №{room.room_number}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.rooms_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="period_start">
                                        Период *
                                    </Label>
                                    <select
                                        id="period_start"
                                        name="period_start"
                                        className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                        required
                                        disabled={!roomId}
                                        value={periodStart}
                                        onChange={(event) =>
                                            setPeriodStart(event.target.value)
                                        }
                                    >
                                        <option value="" disabled>
                                            {roomId
                                                ? 'Выберите период'
                                                : 'Сначала выберите комнату'}
                                        </option>
                                        {availablePeriods.map((period) => (
                                            <option
                                                key={period.period_start}
                                                value={period.period_start}
                                            >
                                                {period.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.period_start} />
                                </div>

                                {selectedPeriod && (
                                    <>
                                        <input
                                            type="hidden"
                                            name="contracts_id"
                                            value={
                                                selectedPeriod.contracts_id
                                            }
                                        />

                                        <div className="rounded-lg bg-gray-50 p-4 text-sm">
                                            <p>
                                                <span className="text-gray-500">
                                                    Договор:{' '}
                                                </span>
                                                №
                                                {
                                                    selectedPeriod.contract_number
                                                }
                                            </p>
                                        </div>
                                    </>
                                )}

                                <div className="space-y-4 rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium">
                                        Холодная вода
                                    </p>
                                    <div className="grid gap-2">
                                        <Label htmlFor="cold_water">
                                            Показание (м³)
                                        </Label>
                                        <Input
                                            id="cold_water"
                                            name="cold_water"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            placeholder="0.000"
                                        />
                                        <InputError
                                            message={errors.cold_water}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="cold_water_photo">
                                            Фото счётчика
                                        </Label>
                                        <Input
                                            id="cold_water_photo"
                                            name="cold_water_photo"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                        />
                                        <InputError
                                            message={errors.cold_water_photo}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-4 rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium">
                                        Горячая вода
                                    </p>
                                    <div className="grid gap-2">
                                        <Label htmlFor="hot_water">
                                            Показание (м³)
                                        </Label>
                                        <Input
                                            id="hot_water"
                                            name="hot_water"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            placeholder="0.000"
                                        />
                                        <InputError
                                            message={errors.hot_water}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="hot_water_photo">
                                            Фото счётчика
                                        </Label>
                                        <Input
                                            id="hot_water_photo"
                                            name="hot_water_photo"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                        />
                                        <InputError
                                            message={errors.hot_water_photo}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-4 rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium">
                                        Электроэнергия
                                    </p>
                                    <div className="grid gap-2">
                                        <Label htmlFor="electricity">
                                            Показание (кВт·ч)
                                        </Label>
                                        <Input
                                            id="electricity"
                                            name="electricity"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            placeholder="0.000"
                                        />
                                        <InputError
                                            message={errors.electricity}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="electricity_photo">
                                            Фото счётчика
                                        </Label>
                                        <Input
                                            id="electricity_photo"
                                            name="electricity_photo"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                        />
                                        <InputError
                                            message={errors.electricity_photo}
                                        />
                                    </div>
                                </div>

                                <DialogFooter className="gap-2 sm:gap-0">
                                    <DialogClose asChild>
                                        <Button type="button" variant="outline">
                                            Отмена
                                        </Button>
                                    </DialogClose>
                                    <Button
                                        type="submit"
                                        disabled={
                                            processing ||
                                            !roomId ||
                                            !periodStart
                                        }
                                    >
                                        {processing
                                            ? 'Сохранение...'
                                            : 'Сохранить'}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                )}
            </DialogContent>
        </Dialog>
    );
}
