import { Form } from '@inertiajs/react';
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
import renterRoutes from '@/routes/renter';
import { meterTypeOptions } from '@/types';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RenterMeterReadingFormDialog({
    open,
    onOpenChange,
}: Props) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Передать показание</DialogTitle>
                </DialogHeader>

                <Form
                    action={renterRoutes.meterReadings.store.url()}
                    method="post"
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="renter_type">Тип счётчика *</Label>
                                <select
                                    id="renter_type"
                                    name="type"
                                    defaultValue="cold_water"
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
                                    <Label htmlFor="renter_reading_date">
                                        Дата *
                                    </Label>
                                    <Input
                                        id="renter_reading_date"
                                        name="reading_date"
                                        type="date"
                                        required
                                    />
                                    <InputError message={errors.reading_date} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="renter_value">
                                        Показание *
                                    </Label>
                                    <Input
                                        id="renter_value"
                                        name="value"
                                        type="number"
                                        min={0}
                                        step={0.001}
                                        required
                                        placeholder="123.456"
                                    />
                                    <InputError message={errors.value} />
                                </div>
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Отправить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
