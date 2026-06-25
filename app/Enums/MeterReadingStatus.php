<?php

namespace App\Enums;

enum MeterReadingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На рассмотрении',
            self::Approved => 'Подтверждено',
            self::Rejected => 'Отклонено',
            self::Archived => 'Архив',
        };
    }

    public function isActive(): bool
    {
        return $this !== self::Archived;
    }
}
