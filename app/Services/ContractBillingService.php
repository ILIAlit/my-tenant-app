<?php

namespace App\Services;

use App\Models\Contract;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ContractBillingService
{
    public function paymentDueDate(Contract $contract, CarbonInterface $chargeDate): CarbonInterface
    {
        $billingDay = $contract->start_date->day;
        $dueMonth = Carbon::parse($chargeDate)->startOfMonth()->addMonth();
        $day = min($billingDay, $dueMonth->daysInMonth);

        return $dueMonth->addDays($day - 1)->startOfDay();
    }

    public function paymentDueDateForRenter(int $userId, CarbonInterface $chargeDate): ?CarbonInterface
    {
        $contract = Contract::query()
            ->where('user_id', $userId)
            ->first();

        if ($contract === null) {
            return null;
        }

        return $this->paymentDueDate($contract, $chargeDate);
    }
}
