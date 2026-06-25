import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import renters from '@/routes/renters';
import type { MeterType, RenterInitialMeterReadings } from '@/types';
import { meterTypeLabels } from '@/types';

type Props = {
    renterId: number;
    initialMeterReadings: RenterInitialMeterReadings;
    onCancel: () => void;
    onSuccess: () => void;
};

const meterTypes: MeterType[] = ['cold_water', 'hot_water', 'electricity'];

export default function RenterInitialMeterReadingsForm({
    renterId,
    initialMeterReadings,
    onCancel,
    onSuccess,
}: Props) {
    return (
        <Form
            action={renters.initialMeterReadings.url(renterId)}
            method="put"
            options={{ preserveScroll: true }}
            className="space-y-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/50 p-4"
            onSuccess={onSuccess}
        >
            {({ processing, errors }) => (
                <>
                    {meterTypes.map((type) => {
                        const reading = initialMeterReadings[type];

                        return (
                            <div
                                key={type}
                                className="space-y-3 rounded-lg border border-gray-200 bg-white p-4"
                            >
                                <h4 className="font-medium">
                                    {meterTypeLabels[type]}
                                </h4>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor={`${type}_value`}>
                                            Показание
                                        </Label>
                                        <Input
                                            id={`${type}_value`}
                                            name={`readings[${type}][value]`}
                                            type="number"
                                            min={0}
                                            step={0.001}
                                            defaultValue={reading?.value ?? ''}
                                            placeholder="0.000"
                                        />
                                        <InputError
                                            message={
                                                errors[
                                                    `readings.${type}.value`
                                                ]
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor={`${type}_reading_date`}>
                                            Дата
                                        </Label>
                                        <Input
                                            id={`${type}_reading_date`}
                                            name={`readings[${type}][reading_date]`}
                                            type="date"
                                            defaultValue={
                                                reading?.reading_date ?? ''
                                            }
                                        />
                                        <InputError
                                            message={
                                                errors[
                                                    `readings.${type}.reading_date`
                                                ]
                                            }
                                        />
                                    </div>
                                </div>
                            </div>
                        );
                    })}

                    <p className="text-xs text-muted-foreground">
                        Оставьте поля пустыми, чтобы удалить начальное
                        показание для счётчика. Начальные показания
                        используются как база для расчёта расхода.
                    </p>

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            Сохранить
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onCancel}
                        >
                            Отмена
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
