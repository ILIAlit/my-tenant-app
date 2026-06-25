<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Free = 'free';
    case Repair = 'repair';
    case Occupied = 'occupied';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Свободна',
            self::Repair => 'Ремонт',
            self::Occupied => 'Занята',
        };
    }
}
