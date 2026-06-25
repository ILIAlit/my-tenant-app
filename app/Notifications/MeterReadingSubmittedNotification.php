<?php

namespace App\Notifications;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeterReadingSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public MeterReading $reading) {}

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
        $this->reading->loadMissing('renter');

        return [
            'type' => 'meter_reading_submitted',
            'title' => __('Новые показания на подтверждение'),
            'message' => __('Арендатор :renter отправил показания: :type, дата :date.', [
                'renter' => $this->formatFullName($this->reading->renter),
                'type' => $this->reading->type->label(),
                'date' => $this->reading->reading_date->format('d.m.Y'),
            ]),
            'url' => '/meter-readings',
        ];
    }

    private function formatFullName(User $user): string
    {
        return trim(implode(' ', array_filter([
            $user->last_name,
            $user->name,
            $user->middle_name,
        ])));
    }
}
