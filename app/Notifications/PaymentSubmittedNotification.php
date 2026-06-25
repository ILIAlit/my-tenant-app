<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification
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
        $this->payment->loadMissing('charge.renter');

        return [
            'type' => 'payment_submitted',
            'title' => __('Новый платёж на подтверждение'),
            'message' => __('Арендатор :renter отправил платёж на сумму :amount BYN.', [
                'renter' => $this->formatFullName($this->payment->charge->renter),
                'amount' => number_format((float) $this->payment->amount, 2, '.', ''),
            ]),
            'url' => '/payments',
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
