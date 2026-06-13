<?php

namespace App\Notifications;

use App\Enums\PaymentStatus;
use App\Models\Invoices;
use App\Models\Payments;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Payments $payment,
        private Invoices $invoice,
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
        $status = $this->payment->status instanceof PaymentStatus
            ? $this->payment->status
            : PaymentStatus::from((string) $this->payment->status);

        return [
            'type' => 'payment',
            'status' => $status->value,
            'title' => $this->title($status),
            'message' => $this->message($status),
            'amount' => (int) $this->payment->amount,
            'invoice_name' => $this->invoice->name,
            'rejection_reason' => $this->payment->rejection_reason,
            'url' => route('payments.get'),
        ];
    }

    private function title(PaymentStatus $status): string
    {
        return match ($status) {
            PaymentStatus::Approved => 'Платёж одобрен',
            PaymentStatus::Rejected => 'Платёж отклонён',
            default => 'Статус платежа изменён',
        };
    }

    private function message(PaymentStatus $status): string
    {
        $amount = number_format((int) $this->payment->amount, 0, '.', ' ');

        return match ($status) {
            PaymentStatus::Approved => "Ваш платёж на {$amount} ₽ по начислению «{$this->invoice->name}» одобрен.",
            PaymentStatus::Rejected => "Ваш платёж на {$amount} ₽ по начислению «{$this->invoice->name}» отклонён.".
                ($this->payment->rejection_reason ? " Причина: {$this->payment->rejection_reason}" : ''),
            default => "Статус платежа по начислению «{$this->invoice->name}» изменён.",
        };
    }
}
