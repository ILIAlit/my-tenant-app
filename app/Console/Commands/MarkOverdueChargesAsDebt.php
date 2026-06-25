<?php

namespace App\Console\Commands;

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Services\ChargePaymentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:mark-overdue-charges-as-debt {--date= : Дата запуска (Y-m-d)}')]
#[Description('Перевод просроченных начислений в статус «Долг»')]
class MarkOverdueChargesAsDebt extends Command
{
    public function handle(ChargePaymentService $chargePaymentService): int
    {
        $today = Carbon::parse($this->option('date') ?? now())->startOfDay();
        $markedCount = 0;
        $revertedCount = 0;
        $skippedCount = 0;

        Charge::query()
            ->where('status', ChargeStatus::Debt)
            ->whereNotNull('last_payment_date')
            ->whereDate('last_payment_date', '>', $today)
            ->with('payments:id,charge_id,amount,status')
            ->orderBy('id')
            ->chunkById(100, function ($charges) use ($chargePaymentService, &$revertedCount, &$skippedCount): void {
                foreach ($charges as $charge) {
                    if ($chargePaymentService->remainingAmount($charge) <= 0) {
                        $skippedCount++;

                        continue;
                    }

                    $charge->update(['status' => ChargeStatus::Unpaid]);
                    $revertedCount++;
                }
            });

        Charge::query()
            ->where('status', ChargeStatus::Unpaid)
            ->whereNotNull('last_payment_date')
            ->whereDate('last_payment_date', '<=', $today)
            ->with('payments:id,charge_id,amount,status')
            ->orderBy('id')
            ->chunkById(100, function ($charges) use ($chargePaymentService, &$markedCount, &$skippedCount): void {
                foreach ($charges as $charge) {
                    if ($chargePaymentService->remainingAmount($charge) <= 0) {
                        $skippedCount++;

                        continue;
                    }

                    $charge->update(['status' => ChargeStatus::Debt]);
                    $markedCount++;
                }
            });

        $this->info("Переведено в долг: {$markedCount}, возвращено в ожидание: {$revertedCount}, пропущено: {$skippedCount}.");

        return self::SUCCESS;
    }
}
