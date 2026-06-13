<?php

namespace App\Console\Commands;

use App\Models\Invoices;
use App\Notifications\InvoiceDueSoonNotification;
use Illuminate\Console\Command;

class NotifyInvoicesDueSoon extends Command
{
    protected $signature = 'invoices:notify-due-soon
                            {--days=3 : За сколько дней до срока оплаты отправлять напоминание}';

    protected $description = 'Уведомляет арендаторов о приближении срока оплаты начислений';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $sent = 0;

        $invoices = Invoices::query()
            ->whereNotNull('user_id')
            ->whereColumn('paid_price', '<', 'total_price')
            ->with('user')
            ->get();

        foreach ($invoices as $invoice) {
            if ($invoice->user === null) {
                continue;
            }

            if ($invoice->daysUntilDue() !== $days) {
                continue;
            }

            if ($invoice->hasPaymentUnderReview()) {
                continue;
            }

            if ($this->alreadyNotified($invoice)) {
                continue;
            }

            $invoice->user->notify(new InvoiceDueSoonNotification($invoice, $days));
            $sent++;
        }

        $this->info("Отправлено напоминаний: {$sent}.");

        return self::SUCCESS;
    }

    private function alreadyNotified(Invoices $invoice): bool
    {
        return $invoice->user
            ->notifications()
            ->where('type', InvoiceDueSoonNotification::class)
            ->where('data->invoice_id', $invoice->id)
            ->exists();
    }
}
