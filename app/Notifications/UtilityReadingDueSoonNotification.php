<?php

namespace App\Notifications;

use App\Models\Rooms;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UtilityReadingDueSoonNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $period
     */
    public function __construct(
        private Rooms $room,
        private array $period,
        private int $daysLeft,
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
        return [
            'type' => 'utility_reading_due_soon',
            'status' => 'due_soon',
            'title' => 'Пора передать показания',
            'message' => $this->message(),
            'rooms_id' => $this->room->id,
            'room_number' => $this->room->number,
            'period_start' => $this->period['start']->format('Y-m-d'),
            'period' => $this->periodLabel(),
            'days_left' => $this->daysLeft,
            'url' => route('utility-readings.get'),
        ];
    }

    private function message(): string
    {
        return "По комнате № {$this->room->number} нужно передать показания счётчиков за период {$this->periodLabel()}. ".
            "До конца срока осталось {$this->daysLeft} ".$this->dayWord($this->daysLeft).'.';
    }

    private function periodLabel(): string
    {
        return $this->period['start']->format('d.m.Y')
            .' — '
            .$this->period['end']->format('d.m.Y');
    }

    private function dayWord(int $days): string
    {
        $mod100 = $days % 100;
        $mod10 = $days % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return 'дней';
        }

        if ($mod10 === 1) {
            return 'день';
        }

        if ($mod10 >= 2 && $mod10 <= 4) {
            return 'дня';
        }

        return 'дней';
    }
}
