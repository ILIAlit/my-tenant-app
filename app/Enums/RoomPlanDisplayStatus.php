<?php

namespace App\Enums;

enum RoomPlanDisplayStatus: string
{
    case Free = 'free';
    case Occupied = 'occupied';
    case Repair = 'repair';
    case Debt = 'debt';
    case AwaitingPayment = 'awaiting_payment';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Свободна',
            self::Occupied => 'Занята',
            self::Repair => 'Ремонт',
            self::Debt => 'Долг',
            self::AwaitingPayment => 'Ожидает оплаты',
        };
    }
}
