<?php

namespace App\Models;

use App\Enums\MeterType;
use App\Enums\RoomType;
use Database\Factories\MeterTariffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'room_type',
    'type',
    'price_per_unit',
])]
class MeterTariff extends Model
{
    /** @use HasFactory<MeterTariffFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'room_type' => RoomType::class,
            'type' => MeterType::class,
            'price_per_unit' => 'decimal:4',
        ];
    }
}
