<?php

namespace App\Notifications;

use App\Models\Charge;
use App\Services\ChargePaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChargePaymentDueReminderNotification extends Notification
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
        $remaining = app(ChargePaymentService::class)->remainingAmount($this->charge);

        return [
            'type' => 'charge_payment_due_reminder',
            'charge_id' => $this->charge->id,
            'title' => __('Напоминание об оплате'),
            'message' => __('Срок оплаты начисления на сумму :amount BYN истекает :date (осталось 3 дня).', [
                'amount' => number_format($remaining, 2, '.', ''),
                'date' => $this->charge->last_payment_date?->format('d.m.Y') ?? '—',
            ]),
            'url' => '/charges/my',
        ];
    }
}
