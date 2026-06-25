<?php

namespace App\Console\Commands;

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Enums\UserRole;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\RenterService;
use App\Models\User;
use App\Notifications\ChargeCreatedNotification;
use App\Services\ContractBillingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Signature('app:create-charges {--date= : Дата начисления (Y-m-d)}')]
#[Description('Создание ежемесячных начислений для арендаторов')]
class CreateCharges extends Command
{
    public function handle(ContractBillingService $contractBillingService): int
    {
        $today = Carbon::parse($this->option('date') ?? now())->startOfDay();
        $createdCount = 0;
        $skippedCount = 0;

        $renters = User::query()
            ->where('role', UserRole::RENTER)
            ->whereHas('contract')
            ->with(['contract', 'renterServices'])
            ->get();

        foreach ($renters as $renter) {
            $result = $this->createChargeForRenter($renter, $today, $contractBillingService);

            if ($result) {
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("Создано начислений: {$createdCount}, пропущено: {$skippedCount}.");

        return self::SUCCESS;
    }

    private function createChargeForRenter(
        User $renter,
        Carbon $today,
        ContractBillingService $contractBillingService,
    ): bool {
        /** @var Contract $contract */
        $contract = $renter->contract;

        $billingDate = $this->billingDateForMonth($today, $contract->start_date->day);

        if (! $billingDate->isSameDay($today)) {
            return false;
        }

        if ($billingDate->lt($contract->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($contract->end_date !== null && $billingDate->gt($contract->end_date->copy()->startOfDay())) {
            return false;
        }

        if ($this->chargeExistsForPeriod($renter->id, $billingDate)) {
            return false;
        }

        $totalAmount = $this->calculateTotalAmount($contract, $renter->renterServices);
        $paymentDueDate = $contractBillingService->paymentDueDate($contract, $billingDate);

        $charge = new Charge([
            'user_id' => $renter->id,
            'category' => ChargeCategory::Rent,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'last_payment_date' => $paymentDueDate->toDateString(),
            'status' => ChargeStatus::Unpaid,
        ]);
        $charge->created_at = $billingDate;
        $charge->updated_at = $billingDate;
        $charge->save();

        $renter->notify(new ChargeCreatedNotification($charge));

        return true;
    }

    private function billingDateForMonth(Carbon $date, int $billingDay): Carbon
    {
        $daysInMonth = $date->daysInMonth;
        $day = min($billingDay, $daysInMonth);

        return $date->copy()->startOfMonth()->addDays($day - 1)->startOfDay();
    }

    private function chargeExistsForPeriod(int $userId, Carbon $billingDate): bool
    {
        return Charge::query()
            ->where('user_id', $userId)
            ->whereYear('created_at', $billingDate->year)
            ->whereMonth('created_at', $billingDate->month)
            ->exists();
    }

    /**
     * @param  Collection<int, RenterService>  $services
     */
    private function calculateTotalAmount(Contract $contract, Collection $services): string
    {
        $servicesTotal = $services
            ->where('is_active', true)
            ->sum(fn ($service) => (float) $service->price);

        return number_format((float) $contract->monthly_rent + $servicesTotal, 2, '.', '');
    }
}
