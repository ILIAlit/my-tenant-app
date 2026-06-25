<?php

namespace App\Services;

use App\Enums\ChargeStatus;
use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\Payment;
use App\Notifications\PaymentApprovedNotification;
use App\Notifications\PaymentRejectedNotification;

class ChargePaymentService
{
    public function remainingAmount(Charge $charge): float
    {
        $committed = $charge->payments()
            ->whereIn('status', [
                PaymentStatus::Approved->value,
                PaymentStatus::Pending->value,
            ])
            ->sum('amount');

        return max(0, round((float) $charge->total_amount - (float) $committed, 2));
    }

    public function canAcceptPayment(Charge $charge): bool
    {
        if ($charge->status === ChargeStatus::Paid || $charge->status === ChargeStatus::Archived) {
            return false;
        }

        return $this->remainingAmount($charge) > 0;
    }

    public function syncCharge(Charge $charge): void
    {
        if ($charge->status === ChargeStatus::Archived) {
            return;
        }
        $approvedSum = (float) $charge->payments()
            ->where('status', PaymentStatus::Approved->value)
            ->sum('amount');

        $hasPending = $charge->payments()
            ->where('status', PaymentStatus::Pending->value)
            ->exists();

        $charge->paid_amount = number_format($approvedSum, 2, '.', '');

        if ($approvedSum >= (float) $charge->total_amount) {
            $charge->status = ChargeStatus::Paid;
        } elseif ($hasPending) {
            $charge->status = ChargeStatus::Pending;
        } else {
            $charge->status = $charge->isOverdue()
                ? ChargeStatus::Debt
                : ChargeStatus::Unpaid;
        }

        $charge->save();
    }

    public function approve(Payment $payment): void
    {
        $payment->loadMissing('charge.renter');
        $payment->update(['status' => PaymentStatus::Approved]);
        $this->syncCharge($payment->charge);
        $payment->charge->renter->notify(new PaymentApprovedNotification($payment));
    }

    public function reject(Payment $payment): void
    {
        $payment->loadMissing('charge.renter');
        $payment->update(['status' => PaymentStatus::Rejected]);
        $this->syncCharge($payment->charge);
        $payment->charge->renter->notify(new PaymentRejectedNotification($payment));
    }
}
