<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;

class HousePlanService
{
    /**
     * @return array{
     *     floors: list<int>,
     *     rooms: list<array{
     *         id: int,
     *         number: string,
     *         floor: int|null,
     *         area: float,
     *         type: string,
     *         display_status: string,
     *         renter_name: string|null,
     *     }>,
     * }
     */
    public function get(): array
    {
        $rooms = Room::query()
            ->with('renter.charges')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        return [
            'floors' => $rooms
                ->pluck('floor')
                ->filter(fn (?int $floor): bool => $floor !== null)
                ->unique()
                ->sort()
                ->values()
                ->all(),
            'rooms' => $rooms->map(fn (Room $room): array => [
                'id' => $room->id,
                'type' => $room->type->value,
                'number' => $room->number,
                'floor' => $room->floor,
                'area' => (float) $room->area,
                'display_status' => $room->planDisplayStatus()->value,
                'renter_name' => $room->renter ? $this->formatFullName($room->renter) : null,
            ])->values()->all(),
        ];
    }

    private function formatFullName(User $renter): string
    {
        return trim(implode(' ', array_filter([
            $renter->last_name,
            $renter->name,
            $renter->middle_name,
        ])));
    }
}
