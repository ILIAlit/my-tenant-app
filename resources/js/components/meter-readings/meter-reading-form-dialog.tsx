import { Form, Link } from '@inertiajs/react';
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
import meterReadings from '@/routes/meter-readings';
import type { MeterReading, MeterReadingRenterOption } from '@/types';
import { meterTypeOptions } from '@/types';

type Props = {
    meterReading?: MeterReading | null;
    renters: MeterReadingRenterOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function MeterReadingFormDialog({
    meterReading = null,
    renters,
    open,
    onOpenChange,
}: Props) {
    const isEditing = meterReading !== null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing
                            ? 'Редактировать показание'
                            : 'Добавить показание'}
                    </DialogTitle>
                </DialogHeader>

                <Form
                    action={
                        isEditing
                            ? meterReadings.update.url(meterReading.id)
                            : meterReadings.create.url()
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
                                <Label htmlFor="user_id">Арендатор *</Label>
                                <select
                                    id="user_id"
                                    name="user_id"
                                    defaultValue={
                                        meterReading?.user_id ??
                                        renters[0]?.id ??
                                        ''
                                    }
                                    required
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="" disabled>
                                        Выберите арендатора
                                    </option>
                                    {renters.map((renter) => (
                                        <option
                                            key={renter.id}
                                            value={renter.id}
                                        >
                                            {renter.full_name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.user_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="type">Тип счётчика *</Label>
                                <select
                                    id="type"
                                    name="type"
                                    defaultValue={
                                        meterReading?.type ?? 'cold_water'
                                    }
                                    required
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    {meterTypeOptions.map((option) => (
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

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="reading_date">Дата *</Label>
                                    <Input
                                        id="reading_date"
                                        name="reading_date"
                                        type="date"
                                        defaultValue={
                                            meterReading?.reading_date ?? ''
                                        }
                                        required
                                    />
                                    <InputError message={errors.reading_date} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="value">Показание *</Label>
                                    <Input
                                        id="value"
                                        name="value"
                                        type="number"
                                        min={0}
                                        step={0.001}
                                        defaultValue={meterReading?.value ?? ''}
                                        required
                                        placeholder="123.456"
                                    />
                                    <InputError message={errors.value} />
                                </div>
                            </div>

                            <DialogFooter className="content-betwin gap-2">
                                {isEditing && (
                                    <DialogClose asChild>
                                        <Button type="button" variant="outline">
                                            <Link
                                                href={meterReadings.destroy(
                                                    meterReading.id,
                                                )}
                                            >
                                                Удалить
                                            </Link>
                                        </Button>
                                    </DialogClose>
                                )}

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
