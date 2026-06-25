<?php

namespace App\Notifications;

use App\Models\MeterReading;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeterReadingApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public MeterReading $meterReading) {}

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
            'type' => 'meter_reading_approved',
            'title' => __('Показание подтверждено'),
            'message' => __('Ваше показание счётчика «:type» подтверждено.', [
                'type' => $this->meterReading->type->label(),
            ]),
            'url' => '/meter-readings/my',
        ];
    }
}
