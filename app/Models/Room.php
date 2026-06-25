<?php

namespace App\Models;

use App\Enums\ChargeStatus;
use App\Enums\RoomPlanDisplayStatus;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type',
    'user_id',
    'number',
    'floor',
    'area',
    'status',
    'last_repair_date',
    'notes',
])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RoomType::class,
            'floor' => 'integer',
            'area' => 'decimal:2',
            'status' => RoomStatus::class,
            'last_repair_date' => 'date:Y-m-d',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function planDisplayStatus(): RoomPlanDisplayStatus
    {
        return match ($this->status) {
            RoomStatus::Free => RoomPlanDisplayStatus::Free,
            RoomStatus::Repair => RoomPlanDisplayStatus::Repair,
            RoomStatus::Occupied => $this->occupiedPlanDisplayStatus(),
        };
    }

    private function occupiedPlanDisplayStatus(): RoomPlanDisplayStatus
    {
        if ($this->user_id === null || $this->renter === null) {
            return RoomPlanDisplayStatus::Occupied;
        }

        $charges = $this->renter->charges;

        foreach ($charges as $charge) {
            if ($charge->displayStatus() === ChargeStatus::Debt->value) {
                return RoomPlanDisplayStatus::Debt;
            }
        }

        foreach ($charges as $charge) {
            $displayStatus = $charge->displayStatus();

            if (in_array($displayStatus, [ChargeStatus::Pending->value, ChargeStatus::Unpaid->value], true)) {
                return RoomPlanDisplayStatus::AwaitingPayment;
            }
        }

        return RoomPlanDisplayStatus::Occupied;
    }
}
