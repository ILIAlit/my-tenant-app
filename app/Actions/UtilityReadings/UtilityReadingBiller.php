<?php

namespace App\Actions\UtilityReadings;

use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\Rooms;
use App\Models\UtilityReading;
use App\Models\UtilityTariff;
use RuntimeException;

class UtilityReadingBiller
{
    /**
     * @return array{
     *     utility_amount: int,
     *     cold_water_consumption: ?float,
     *     hot_water_consumption: ?float,
     *     electricity_consumption: ?float,
     *     invoices_id: ?int
     * }
     */
    public function bill(UtilityReading $reading, UtilityTariff $tariff): array
    {
        $reading->loadMissing(['room', 'contract']);

        $room = $reading->room;

        if ($room === null || $room->user_id === null) {
            throw new RuntimeException(__('Комната не привязана к арендатору.'));
        }

        $contract = $reading->contract;

        if ($contract === null) {
            throw new RuntimeException(__('Договор для показаний не найден.'));
        }

        $consumption = $this->resolveConsumption($reading);
        $utilityAmount = $this->calculateAmount($consumption, $tariff);

        $invoiceId = $utilityAmount > 0
            ? $this->createUtilityInvoice($room, $contract, $reading, $utilityAmount)->id
            : null;

        return [
            'utility_amount' => $utilityAmount,
            'cold_water_consumption' => $consumption['cold_water'],
            'hot_water_consumption' => $consumption['hot_water'],
            'electricity_consumption' => $consumption['electricity'],
            'invoices_id' => $invoiceId,
        ];
    }

    /**
     * Потребление за период — это введённые арендатором показания.
     *
     * @return array{cold_water: ?float, hot_water: ?float, electricity: ?float}
     */
    private function resolveConsumption(UtilityReading $reading): array
    {
        $result = [
            'cold_water' => null,
            'hot_water' => null,
            'electricity' => null,
        ];

        foreach (array_keys($result) as $field) {
            if ($reading->{$field} !== null) {
                $result[$field] = max(0, (float) $reading->{$field});
            }
        }

        return $result;
    }

    /**
     * @param  array{cold_water: ?float, hot_water: ?float, electricity: ?float}  $consumption
     */
    private function calculateAmount(array $consumption, UtilityTariff $tariff): int
    {
        $amount = 0.0;

        if ($consumption['cold_water'] !== null) {
            $amount += $consumption['cold_water'] * (float) $tariff->cold_water_rate;
        }

        if ($consumption['hot_water'] !== null) {
            $amount += $consumption['hot_water'] * (float) $tariff->hot_water_rate;
        }

        if ($consumption['electricity'] !== null) {
            $amount += $consumption['electricity'] * (float) $tariff->electricity_rate;
        }

        return (int) round($amount);
    }

    private function createUtilityInvoice(Rooms $room, Contracts $contract, UtilityReading $reading, int $amount): Invoices
    {
        $createDate = $reading->period_start;

        return Invoices::create([
            'user_id' => $room->user_id,
            'rooms_id' => $room->id,
            'contracts_id' => $contract->id,
            'period_start' => null,
            'name' => $this->invoiceName($room->number, $reading),
            'total_price' => $amount,
            'create_date' => $createDate->format('d.m.Y'),
            'due_date' => $contract->dueDateFor($createDate),
        ]);
    }

    private function invoiceName(int $roomNumber, UtilityReading $reading): string
    {
        $periodLabel = $reading->period_start->format('d.m.Y')
            .' — '
            .$reading->period_end->format('d.m.Y');

        return 'Коммунальные услуги · комната № '.$roomNumber.' ('.$periodLabel.')';
    }
}
