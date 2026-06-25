import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import meterReadings from '@/routes/meter-readings';
import type { MeterTariffType, MeterTariffsByRoomType, RoomType } from '@/types';
import { meterTariffLabels, roomTypeLabels } from '@/types';

type Props = {
    tariffs: MeterTariffsByRoomType;
};

const tariffTypes: MeterTariffType[] = [
    'cold_water',
    'hot_water',
    'electricity',
    'sewage',
];

const roomTypes: RoomType[] = ['room', 'garage'];

const unitLabel = (type: MeterTariffType): string =>
    type === 'electricity' ? 'BYN / кВт·ч' : 'BYN / м³';

export default function MeterTariffsForm({ tariffs }: Props) {
    return (
        <Form
            action={meterReadings.tariffs.update.url()}
            method="put"
            options={{ preserveScroll: true }}
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
                    {roomTypes.map((roomType) => (
                        <div key={roomType} className="space-y-4">
                            <div>
                                <h4 className="font-medium">
                                    {roomTypeLabels[roomType]}
                                </h4>
                                <p className="text-sm text-muted-foreground">
                                    Тарифы для арендаторов с типом помещения «
                                    {roomTypeLabels[roomType].toLowerCase()}»
                                </p>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                {tariffTypes.map((type) => (
                                    <div key={type} className="grid gap-2">
                                        <Label
                                            htmlFor={`tariff_${roomType}_${type}`}
                                        >
                                            {meterTariffLabels[type]}
                                        </Label>
                                        <Input
                                            id={`tariff_${roomType}_${type}`}
                                            name={`tariffs[${roomType}][${type}]`}
                                            type="number"
                                            min={0}
                                            step={0.0001}
                                            defaultValue={tariffs[roomType][type]}
                                            required
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            {unitLabel(type)}
                                            {type === 'sewage' &&
                                                ' (расход холодной + горячей воды)'}
                                        </p>
                                        <InputError
                                            message={
                                                errors[
                                                    `tariffs.${roomType}.${type}`
                                                ]
                                            }
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}

                    <Button type="submit" disabled={processing}>
                        Сохранить тарифы
                    </Button>
                </>
            )}
        </Form>
    );
}
