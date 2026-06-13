<?php

namespace App\Notifications;

use App\Models\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceDueSoonNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Invoices $invoice,
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
            'type' => 'invoice_due_soon',
            'status' => 'due_soon',
            'title' => 'Скоро срок оплаты',
            'message' => $this->message(),
            'invoice_id' => $this->invoice->id,
            'invoice_name' => $this->invoice->name,
            'due_date' => $this->invoice->dueDate()?->format('d.m.Y'),
            'days_left' => $this->daysLeft,
            'amount' => $this->invoice->remainingAmount(),
            'url' => route('invoices.get'),
        ];
    }

    private function message(): string
    {
        $amount = number_format($this->invoice->remainingAmount(), 0, '.', ' ');
        $dueDate = $this->invoice->dueDate()?->format('d.m.Y');

        return "До срока оплаты начисления «{$this->invoice->name}» осталось {$this->daysLeft} ".
            $this->dayWord($this->daysLeft).
            " (до {$dueDate}). К оплате {$amount} ₽.";
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
