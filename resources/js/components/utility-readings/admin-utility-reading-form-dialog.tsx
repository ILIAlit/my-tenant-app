import { Form } from '@inertiajs/react';
import AdminUtilityReadingsController from '@/actions/App/Http/Controllers/Admin/UtilityReadings/AdminUtilityReadingsController';
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
import type { UtilityReading } from '@/types';

type AdminUtilityReadingFormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    reading: UtilityReading | null;
    fromRoom?: boolean;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

export default function AdminUtilityReadingFormDialog({
    open,
    onOpenChange,
    reading,
    fromRoom = false,
}: AdminUtilityReadingFormDialogProps) {
    if (!reading) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Редактировать показания</DialogTitle>
                </DialogHeader>

                <Form
                    key={reading.id}
                    {...AdminUtilityReadingsController.update.form(reading.id)}
                    options={{ preserveScroll: true }}
                    className="space-y-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            {fromRoom && (
                                <input
                                    type="hidden"
                                    name="from_room"
                                    value="1"
                                />
                            )}

                            <div className="rounded-lg bg-gray-50 p-4 text-sm">
                                {reading.room && (
                                    <p>
                                        <span className="text-gray-500">
                                            Комната:{' '}
                                        </span>
                                        №{reading.room.number}
                                    </p>
                                )}
                                {reading.contract && (
                                    <p>
                                        <span className="text-gray-500">
                                            Договор:{' '}
                                        </span>
                                        №{reading.contract.number}
                                    </p>
                                )}
                                <p>
                                    <span className="text-gray-500">
                                        Период:{' '}
                                    </span>
                                    {formatDate(reading.period_start)} —{' '}
                                    {formatDate(reading.period_end)}
                                </p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="cold_water">
                                    Холодная вода (м³)
                                </Label>
                                <Input
                                    id="cold_water"
                                    name="cold_water"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    defaultValue={reading.cold_water ?? ''}
                                />
                                <InputError message={errors.cold_water} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="hot_water">
                                    Горячая вода (м³)
                                </Label>
                                <Input
                                    id="hot_water"
                                    name="hot_water"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    defaultValue={reading.hot_water ?? ''}
                                />
                                <InputError message={errors.hot_water} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="electricity">
                                    Электроэнергия (кВт·ч)
                                </Label>
                                <Input
                                    id="electricity"
                                    name="electricity"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    defaultValue={reading.electricity ?? ''}
                                />
                                <InputError message={errors.electricity} />
                            </div>

                            <DialogFooter className="gap-2 sm:gap-0">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Сохранение...' : 'Сохранить'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
