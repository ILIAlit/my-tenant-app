<?php

namespace App\Concerns;

use App\Models\Charge;

trait FormatsChargeBreakdown
{
    /**
     * @return list<array{key: string, label: string, consumption: float, unit: string, tariff: float, amount: float}>|null
     */
    protected function formatChargeBreakdown(Charge $charge): ?array
    {
        if ($charge->breakdown === null) {
            return null;
        }

        return array_map(fn (array $item): array => [
            'key' => $item['key'],
            'label' => $item['label'],
            'consumption' => (float) $item['consumption'],
            'unit' => $item['unit'],
            'tariff' => (float) $item['tariff'],
            'amount' => (float) $item['amount'],
        ], $charge->breakdown);
    }
}
