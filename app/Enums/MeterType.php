<?php

namespace App\Enums;

enum MeterType: string
{
    case HotWater = 'hot_water';
    case ColdWater = 'cold_water';
    case Electricity = 'electricity';
    case Sewage = 'sewage';

    public function label(): string
    {
        return match ($this) {
            self::HotWater => 'Горячая вода',
            self::ColdWater => 'Холодная вода',
            self::Electricity => 'Электричество',
            self::Sewage => 'Канализация',
        };
    }

    public function isMetered(): bool
    {
        return $this !== self::Sewage;
    }

    public function consumptionUnit(): string
    {
        return $this === self::Electricity ? 'кВт·ч' : 'м³';
    }

    /**
     * @return list<self>
     */
    public static function metered(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $type): bool => $type->isMetered(),
        ));
    }

    /**
     * @return list<string>
     */
    public static function meteredValues(): array
    {
        return array_map(fn (self $type): string => $type->value, self::metered());
    }
}
