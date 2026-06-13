import { Form } from '@inertiajs/react';
import AdminUtilityReadingsController from '@/actions/App/Http/Controllers/Admin/UtilityReadings/AdminUtilityReadingsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import type { UtilityReading } from '@/types';

type RejectUtilityReadingDialogProps = {
    reading: UtilityReading;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

export default function RejectUtilityReadingDialog({
    reading,
    open,
    onOpenChange,
}: RejectUtilityReadingDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Отклонить показания</DialogTitle>
                    <DialogDescription>
                        Период {formatDate(reading.period_start)} —{' '}
                        {formatDate(reading.period_end)}
                        {reading.room && ` · Комната №${reading.room.number}`}.
                        Арендатор сможет отправить показания повторно.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...AdminUtilityReadingsController.reject.form(reading.id)}
                    options={{ preserveScroll: true }}
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="rejection_reason">
                                    Причина отклонения *
                                </Label>
                                <textarea
                                    id="rejection_reason"
                                    name="rejection_reason"
                                    rows={3}
                                    required
                                    className="block w-full rounded-md border border-gray-300 px-3 py-2"
                                    placeholder="Например: фото счётчика нечитаемо"
                                />
                                <InputError message={errors.rejection_reason} />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Отмена
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    Отклонить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
