<?php

namespace App\Enums;

enum ChargeStatus: string
{
    case Paid = 'paid';
    case Pending = 'pending';
    case Unpaid = 'unpaid';
    case Debt = 'debt';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Оплачено',
            self::Pending => 'На рассмотрении',
            self::Unpaid => 'Не оплачено',
            self::Debt => 'Долг',
            self::Archived => 'Архив',
        };
    }

    public function isActive(): bool
    {
        return $this !== self::Archived;
    }

    public static function displayLabel(string $displayStatus): string
    {
        return match ($displayStatus) {
            self::Paid->value => 'Оплачено',
            self::Pending->value => 'На рассмотрении',
            self::Unpaid->value => 'Не оплачено',
            self::Debt->value => 'Долг',
            self::Archived->value => 'Архив',
            default => $displayStatus,
        };
    }
}
