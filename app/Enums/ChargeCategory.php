<?php

namespace App\Enums;

enum ChargeCategory: string
{
    case Rent = 'rent';
    case Utilities = 'utilities';
    case ColdWater = 'cold_water';
    case HotWater = 'hot_water';
    case Electricity = 'electricity';
    case Sewage = 'sewage';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Rent => 'Аренда и услуги',
            self::Utilities => 'Коммунальные услуги',
            self::ColdWater => 'Холодная вода',
            self::HotWater => 'Горячая вода',
            self::Electricity => 'Электричество',
            self::Sewage => 'Канализация',
            self::Other => 'Прочее',
        };
    }

    public static function fromMeterType(MeterType $type): self
    {
        return match ($type) {
            MeterType::ColdWater => self::ColdWater,
            MeterType::HotWater => self::HotWater,
            MeterType::Electricity => self::Electricity,
            MeterType::Sewage => self::Sewage,
        };
    }
}
