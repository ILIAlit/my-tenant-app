<?php

namespace App\Services;

use App\Models\MeterReading;
use Illuminate\Support\Collection;

class MeterReadingConsumptionService
{
    /**
     * @param  Collection<int, MeterReading>  $readings
     * @param  Collection<int, MeterReading>  $history
     * @return array<int, array{previous_value: ?float, consumption: ?float}>
     */
    public function calculateForReadings(Collection $readings, Collection $history): array
    {
        $grouped = $this->sortChronologically($history)
            ->groupBy(fn (MeterReading $reading): string => $this->groupKey($reading));

        $result = [];

        foreach ($readings as $reading) {
            $chain = $grouped->get($this->groupKey($reading), collect())->values();
            $index = $chain->search(fn (MeterReading $item): bool => $item->id === $reading->id);

            if ($index === false || $index === 0) {
                $result[$reading->id] = [
                    'previous_value' => null,
                    'consumption' => null,
                ];

                continue;
            }

            $previous = $chain[$index - 1];

            $result[$reading->id] = [
                'previous_value' => (float) $previous->value,
                'consumption' => round((float) $reading->value - (float) $previous->value, 3),
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, MeterReading>  $readings
     * @return Collection<int, MeterReading>
     */
    private function sortChronologically(Collection $readings): Collection
    {
        return $readings->sort(function (MeterReading $first, MeterReading $second): int {
            $dateCompare = $first->reading_date <=> $second->reading_date;

            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            $createdCompare = ($first->created_at?->timestamp ?? 0) <=> ($second->created_at?->timestamp ?? 0);

            if ($createdCompare !== 0) {
                return $createdCompare;
            }

            return $first->id <=> $second->id;
        })->values();
    }

    private function groupKey(MeterReading $reading): string
    {
        return $reading->user_id.'|'.$reading->type->value;
    }
}
