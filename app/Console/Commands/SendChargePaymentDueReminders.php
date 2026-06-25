<?php

namespace App\Console\Commands;

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Notifications\ChargePaymentDueReminderNotification;
use App\Services\ChargePaymentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:send-charge-payment-reminders {--date= : Дата запуска (Y-m-d)} {--days=3 : За сколько дней до срока напоминать}')]
#[Description('Рассылка напоминаний арендаторам о приближающемся сроке оплаты начислений')]
class SendChargePaymentDueReminders extends Command
{
    public function handle(ChargePaymentService $chargePaymentService): int
    {
        $today = Carbon::parse($this->option('date') ?? now())->startOfDay();
        $daysBeforeDue = max(1, (int) $this->option('days'));
        $dueDate = $today->copy()->addDays($daysBeforeDue);

        $sentCount = 0;
        $skippedCount = 0;

        Charge::query()
            ->whereNotNull('last_payment_date')
            ->whereDate('last_payment_date', $dueDate)
            ->where('status', ChargeStatus::Unpaid)
            ->with(['renter', 'payments:id,charge_id,amount,status'])
            ->orderBy('id')
            ->chunkById(100, function ($charges) use ($chargePaymentService, &$sentCount, &$skippedCount): void {
                foreach ($charges as $charge) {
                    if (! $chargePaymentService->canAcceptPayment($charge)) {
                        $skippedCount++;

                        continue;
                    }

                    $renter = $charge->renter;

                    if ($renter === null) {
                        $skippedCount++;

                        continue;
                    }

                    if ($this->reminderAlreadySent($renter, $charge->id)) {
                        $skippedCount++;

                        continue;
                    }

                    $renter->notify(new ChargePaymentDueReminderNotification($charge));
                    $sentCount++;
                }
            });

        $this->info("Отправлено напоминаний: {$sentCount}, пропущено: {$skippedCount}.");

        return self::SUCCESS;
    }

    private function reminderAlreadySent(object $renter, int $chargeId): bool
    {
        return $renter->notifications()
            ->where('type', ChargePaymentDueReminderNotification::class)
            ->where('data->charge_id', $chargeId)
            ->exists();
    }
}
