import { Form } from '@inertiajs/react';
import AdminUtilityReadingsController from '@/actions/App/Http/Controllers/Admin/UtilityReadings/AdminUtilityReadingsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { UtilityTariff } from '@/types';

type UtilityTariffsFormProps = {
    tariffs: UtilityTariff;
};

export default function UtilityTariffsForm({ tariffs }: UtilityTariffsFormProps) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6">
            <div className="mb-4">
                <h3 className="font-medium">Тарифы на коммунальные услуги</h3>
                <p className="text-sm text-gray-500">
                    Сумма рассчитывается при одобрении показаний: расход ×
                    тариф
                </p>
            </div>

            <Form
                {...AdminUtilityReadingsController.updateTariffs.form()}
                options={{ preserveScroll: true }}
                className="grid gap-4 sm:grid-cols-3"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="cold_water_rate">
                                Холодная вода, ₽/м³
                            </Label>
                            <Input
                                id="cold_water_rate"
                                name="cold_water_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                defaultValue={tariffs.cold_water_rate}
                            />
                            <InputError message={errors.cold_water_rate} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="hot_water_rate">
                                Горячая вода, ₽/м³
                            </Label>
                            <Input
                                id="hot_water_rate"
                                name="hot_water_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                defaultValue={tariffs.hot_water_rate}
                            />
                            <InputError message={errors.hot_water_rate} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="electricity_rate">
                                Электроэнергия, ₽/кВт·ч
                            </Label>
                            <Input
                                id="electricity_rate"
                                name="electricity_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                defaultValue={tariffs.electricity_rate}
                            />
                            <InputError message={errors.electricity_rate} />
                        </div>

                        <div className="sm:col-span-3">
                            <Button type="submit" disabled={processing}>
                                Сохранить тарифы
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
