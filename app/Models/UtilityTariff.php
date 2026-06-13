<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cold_water_rate', 'hot_water_rate', 'electricity_rate'])]
class UtilityTariff extends Model
{
    protected $casts = [
        'cold_water_rate' => 'decimal:2',
        'hot_water_rate' => 'decimal:2',
        'electricity_rate' => 'decimal:2',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'cold_water_rate' => 0,
            'hot_water_rate' => 0,
            'electricity_rate' => 0,
        ]);
    }
}
