<?php

namespace App\Notifications;

use App\Enums\UtilityReadingStatus;
use App\Models\UtilityReading;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UtilityReadingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private UtilityReading $reading,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->reading->status instanceof UtilityReadingStatus
            ? $this->reading->status
            : UtilityReadingStatus::from((string) $this->reading->status);

        return [
            'type' => 'utility_reading',
            'status' => $status->value,
            'title' => $this->title($status),
            'message' => $this->message($status),
            'period' => $this->periodLabel(),
            'utility_amount' => (int) $this->reading->utility_amount,
            'rejection_reason' => $this->reading->rejection_reason,
            'url' => route('utility-readings.get'),
        ];
    }

    private function title(UtilityReadingStatus $status): string
    {
        return match ($status) {
            UtilityReadingStatus::Approved => 'Показания одобрены',
            UtilityReadingStatus::Rejected => 'Показания отклонены',
            default => 'Статус показаний изменён',
        };
    }

    private function message(UtilityReadingStatus $status): string
    {
        $period = $this->periodLabel();

        return match ($status) {
            UtilityReadingStatus::Approved => "Показания за период {$period} одобрены.".
                ($this->reading->utility_amount > 0
                    ? ' Начислено '.number_format((int) $this->reading->utility_amount, 0, '.', ' ').' ₽ за коммунальные услуги.'
                    : ''),
            UtilityReadingStatus::Rejected => "Показания за период {$period} отклонены.".
                ($this->reading->rejection_reason ? " Причина: {$this->reading->rejection_reason}" : ''),
            default => "Статус показаний за период {$period} изменён.",
        };
    }

    private function periodLabel(): string
    {
        return $this->reading->period_start->format('d.m.Y')
            .' — '
            .$this->reading->period_end->format('d.m.Y');
    }
}
