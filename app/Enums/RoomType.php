<?php

namespace App\Enums;

enum RoomType: string
{
    case Room = 'room';
    case Garage = 'garage';

    public function label(): string
    {
        return match ($this) {
            self::Room => 'Комната',
            self::Garage => 'Гараж',
        };
    }
}
