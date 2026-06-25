<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

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
            'type' => 'payment_approved',
            'title' => __('Платёж подтверждён'),
            'message' => __('Ваш платёж на сумму :amount BYN подтверждён.', [
                'amount' => number_format((float) $this->payment->amount, 2, '.', ''),
            ]),
            'url' => '/payments/my',
        ];
    }
}
