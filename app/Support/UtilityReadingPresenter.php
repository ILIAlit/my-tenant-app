<?php

namespace App\Support;

use App\Enums\UtilityReadingStatus;
use App\Models\Rooms;
use App\Models\UtilityReading;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UtilityReadingPresenter
{
    /**
     * @return array{
     *     room_id: int,
     *     room_number: int,
     *     readings: Collection<int, UtilityReading>,
     *     availablePeriods: Collection<int, array<string, mixed>>
     * }
     */
    public static function forRoom(Rooms $room): array
    {
        $room->loadMissing([
            'contracts' => fn ($query) => $query->latest('conclusion_date'),
            'utilityReadings' => fn ($query) => $query
                ->with('contract:id,number')
                ->latest('period_start'),
        ]);

        $contract = $room->contracts->first();

        $readings = $room->utilityReadings
            ->each(fn (UtilityReading $reading) => $reading->append([
                'cold_water_photo_url',
                'hot_water_photo_url',
                'electricity_photo_url',
            ]));

        $availablePeriods = collect();

        if ($contract !== null) {
            $existingStarts = $room->utilityReadings
                ->filter(fn (UtilityReading $reading) => in_array($reading->status, [
                    UtilityReadingStatus::Review,
                    UtilityReadingStatus::Approved,
                ], true))
                ->pluck('period_start')
                ->map(fn ($date) => CarbonImmutable::parse($date)->format('Y-m-d'))
                ->all();

            $availablePeriods = collect($contract->billingPeriods())
                ->reject(fn (array $period) => in_array($period['start']->format('Y-m-d'), $existingStarts, true))
                ->reject(fn (array $period) => $period['start']->isFuture())
                ->map(fn (array $period) => [
                    'rooms_id' => $room->id,
                    'room_number' => $room->number,
                    'contracts_id' => $contract->id,
                    'contract_number' => $contract->number,
                    'period_start' => $period['start']->format('Y-m-d'),
                    'period_end' => $period['end']->format('Y-m-d'),
                    'label' => $period['start']->format('d.m.Y').' — '.$period['end']->format('d.m.Y'),
                ])
                ->values();
        }

        return [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'readings' => $readings->values(),
            'availablePeriods' => $availablePeriods,
        ];
    }

    /**
     * @param  Collection<int, Rooms>  $rooms
     * @return list<array<string, mixed>>
     */
    public static function forRooms(Collection $rooms): array
    {
        return $rooms
            ->map(fn (Rooms $room) => self::forRoom($room))
            ->values()
            ->all();
    }
}
