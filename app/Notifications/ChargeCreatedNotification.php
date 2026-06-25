<?php

namespace App\Notifications;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChargeCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Charge $charge) {}

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
            'type' => 'charge_created',
            'title' => __('Новое начисление'),
            'message' => __('Создано начисление на сумму :amount BYN.', [
                'amount' => number_format((float) $this->charge->total_amount, 2, '.', ''),
            ]),
            'url' => '/charges/my',
        ];
    }
}
