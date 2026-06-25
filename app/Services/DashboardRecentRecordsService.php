<?php

namespace App\Services;

use App\Enums\MeterReadingStatus;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\User;

class DashboardRecentRecordsService
{
    private const int LIMIT = 5;

    public function __construct(private MeterReadingBillingService $billingService) {}

    /**
     * @return list<array{
     *     id: int,
     *     amount: float,
     *     status: string,
     *     created_at: string,
     *     charge: array{id: int, total_amount: float, created_at: string},
     *     renter: array{id: int, full_name: string},
     * }>
     */
    public function recentPayments(): array
    {
        return Payment::query()
            ->whereHas('charge', fn ($query) => $query
                ->active()
                ->whereNotNull('user_id'))
            ->with([
                'charge:id,user_id,total_amount,created_at',
                'charge.renter:id,last_name,name,middle_name',
            ])
            ->orderByDesc('created_at')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'status' => $payment->status->value,
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
                'charge' => [
                    'id' => $payment->charge->id,
                    'total_amount' => (float) $payment->charge->total_amount,
                    'created_at' => $payment->charge->created_at->format('Y-m-d'),
                ],
                'renter' => [
                    'id' => $payment->charge->renter->id,
                    'full_name' => $this->formatFullName($payment->charge->renter),
                ],
            ])
            ->all();
    }

    /**
     * @return list<array{
     *     id: int,
     *     type: string,
     *     reading_date: string,
     *     value: float,
     *     status: string,
     *     consumption: float|null,
     *     renter: array{
     *         id: int,
     *         full_name: string,
     *         room: array{number: string, floor: int}|null,
     *     },
     * }>
     */
    public function recentMeterReadings(): array
    {
        $readings = MeterReading::query()
            ->active()
            ->whereNotNull('user_id')
            ->where('is_initial', false)
            ->with([
                'renter:id,last_name,name,middle_name',
                'renter.room:id,user_id,type,number,floor',
            ])
            ->orderByDesc('reading_date')
            ->orderByDesc('created_at')
            ->limit(self::LIMIT)
            ->get();

        if ($readings->isEmpty()) {
            return [];
        }

        $history = MeterReading::query()
            ->forConsumption()
            ->whereIn('user_id', $readings->pluck('user_id')->filter()->unique())
            ->get();

        $billing = $this->billingService->enrichWithBilling($readings, $history);

        return $readings->map(function (MeterReading $reading) use ($billing): array {
            $data = $billing[$reading->id];
            $showConsumption = in_array($reading->status, [
                MeterReadingStatus::Approved,
                MeterReadingStatus::Pending,
            ], true);

            return [
                'id' => $reading->id,
                'type' => $reading->type->value,
                'reading_date' => $reading->reading_date->format('Y-m-d'),
                'value' => (float) $reading->value,
                'status' => $reading->status->value,
                'consumption' => $showConsumption ? $data['consumption'] : null,
                'renter' => [
                    'id' => $reading->renter->id,
                    'full_name' => $this->formatFullName($reading->renter),
                    'room' => $reading->renter->room ? [
                        'type' => $reading->renter->room->type->value,
                        'number' => $reading->renter->room->number,
                        'floor' => $reading->renter->room->floor,
                    ] : null,
                ],
            ];
        })->all();
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
