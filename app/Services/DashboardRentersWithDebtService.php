<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\User;

class DashboardRentersWithDebtService
{
    /**
     * @return list<array{
     *     id: int,
     *     full_name: string,
     *     debt_amount: float,
     *     room: array{type: string, number: string, floor: int|null}|null,
     * }>
     */
    public function get(): array
    {
        $charges = Charge::query()
            ->active()
            ->whereNotNull('user_id')
            ->with([
                'renter:id,last_name,name,middle_name',
                'renter.room:id,user_id,type,number,floor',
            ])
            ->whereColumn('total_amount', '>', 'paid_amount')
            ->whereNotNull('last_payment_date')
            ->whereDate('last_payment_date', '<=', now())
            ->get();

        return $charges
            ->groupBy('user_id')
            ->map(function ($renterCharges): array {
                /** @var Charge $firstCharge */
                $firstCharge = $renterCharges->first();
                $renter = $firstCharge->renter;

                return [
                    'id' => $renter->id,
                    'full_name' => $this->formatFullName($renter),
                    'debt_amount' => round($renterCharges->sum(
                        fn (Charge $charge): float => (float) $charge->total_amount - (float) $charge->paid_amount,
                    ), 2),
                    'room' => $renter->room ? [
                        'type' => $renter->room->type->value,
                        'number' => $renter->room->number,
                        'floor' => $renter->room->floor,
                    ] : null,
                ];
            })
            ->sortByDesc('debt_amount')
            ->values()
            ->all();
    }

    private function formatFullName(User $renter): string
    {
        return trim(implode(' ', array_filter([
            $renter->last_name,
            $renter->name,
            $renter->middle_name,
        ])));
    }
}
