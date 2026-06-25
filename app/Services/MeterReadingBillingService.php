<?php

namespace App\Services;

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\RoomType;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\MeterTariff;
use App\Models\User;
use App\Notifications\ChargeCreatedNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MeterReadingBillingService
{
    public function __construct(
        private MeterReadingConsumptionService $consumptionService,
        private ContractBillingService $contractBillingService,
    ) {}

    /**
     * @return array<string, array<string, float>>
     */
    public function tariffsGroupedByRoomType(): array
    {
        $grouped = [];

        foreach (RoomType::cases() as $roomType) {
            $grouped[$roomType->value] = [];

            foreach (MeterType::cases() as $type) {
                $grouped[$roomType->value][$type->value] = 0.0;
            }
        }

        MeterTariff::query()
            ->get()
            ->each(function (MeterTariff $tariff) use (&$grouped): void {
                $grouped[$tariff->room_type->value][$tariff->type->value] = (float) $tariff->price_per_unit;
            });

        return $grouped;
    }

    /**
     * @return array<string, float>
     */
    public function tariffsForRoomType(RoomType $roomType): array
    {
        return MeterTariff::query()
            ->where('room_type', $roomType)
            ->get()
            ->mapWithKeys(fn (MeterTariff $tariff): array => [
                $tariff->type->value => (float) $tariff->price_per_unit,
            ])
            ->all();
    }

    /**
     * @param  array<string, array<string, float|int|string|null>>  $tariffs
     */
    public function updateTariffs(array $tariffs): void
    {
        foreach ($tariffs as $roomTypeValue => $typePrices) {
            if (! is_array($typePrices)) {
                continue;
            }

            foreach ($typePrices as $type => $price) {
                MeterTariff::query()
                    ->where('room_type', $roomTypeValue)
                    ->where('type', $type)
                    ->update(['price_per_unit' => $price]);
            }
        }
    }

    public function billApprovedReading(MeterReading $reading): ?Charge
    {
        if ($reading->is_initial || $reading->status !== MeterReadingStatus::Approved) {
            return null;
        }

        $roomType = $this->roomTypeForUser($reading->user_id);
        $tariffs = $this->tariffsForRoomType($roomType);
        $consumption = $this->consumptionForReading($reading);

        if ($consumption !== null && $consumption > 0) {
            $lineAmount = $this->lineAmountForReading($reading, $consumption, $tariffs);

            $reading->update([
                'consumption' => $consumption,
                'charged_amount' => $lineAmount > 0 ? $lineAmount : null,
            ]);
        }

        return $this->syncUtilitiesChargeForPeriod($reading->user_id, $reading->reading_date);
    }

    public function estimatedCost(?float $consumption, MeterReading $reading): ?float
    {
        if ($consumption === null || $consumption <= 0) {
            return null;
        }

        $roomType = $this->roomTypeForUser($reading->user_id);

        $tariff = MeterTariff::query()
            ->where('type', $reading->type)
            ->where('room_type', $roomType)
            ->first();

        if ($tariff === null || (float) $tariff->price_per_unit <= 0) {
            return null;
        }

        return round($consumption * (float) $tariff->price_per_unit, 2);
    }

    /**
     * @param  Collection<int, MeterReading>  $readings
     * @param  Collection<int, MeterReading>  $history
     * @return array<int, array{previous_value: ?float, consumption: ?float, estimated_cost: ?float}>
     */
    public function enrichWithBilling(
        Collection $readings,
        Collection $history,
    ): array {
        $consumption = $this->consumptionService->calculateForReadings($readings, $history);
        $roomTypesByUserId = $this->roomTypesByUserId($readings->pluck('user_id')->unique()->values());
        $tariffsCache = [];
        $result = [];

        foreach ($readings as $reading) {
            $roomType = $roomTypesByUserId[$reading->user_id] ?? RoomType::Room;
            $tariffKey = $roomType->value;

            if (! isset($tariffsCache[$tariffKey])) {
                $tariffsCache[$tariffKey] = $this->tariffsForRoomType($roomType);
            }

            $data = $consumption[$reading->id];
            $consumptionValue = $reading->consumption !== null
                ? (float) $reading->consumption
                : $data['consumption'];

            $estimatedCost = $reading->charged_amount !== null
                ? (float) $reading->charged_amount
                : $this->lineAmountForReading($reading, $consumptionValue, $tariffsCache[$tariffKey]);

            $result[$reading->id] = [
                'previous_value' => $data['previous_value'],
                'consumption' => $consumptionValue,
                'estimated_cost' => $estimatedCost !== null && $estimatedCost > 0 ? $estimatedCost : null,
            ];
        }

        return $result;
    }

    private function syncUtilitiesChargeForPeriod(int $userId, CarbonInterface $readingDate): ?Charge
    {
        $history = MeterReading::query()
            ->forConsumption()
            ->where('user_id', $userId)
            ->get();

        $readings = MeterReading::query()
            ->where('user_id', $userId)
            ->whereDate('reading_date', $readingDate)
            ->where('is_initial', false)
            ->where('status', MeterReadingStatus::Approved)
            ->get();

        if ($readings->isEmpty()) {
            return null;
        }

        $tariffs = $this->tariffsForRoomType($this->roomTypeForUser($userId));
        $consumptionData = $this->consumptionService->calculateForReadings($readings, $history);

        $breakdown = [];
        $utilitiesAmount = 0.0;
        $coldConsumption = 0.0;
        $hotConsumption = 0.0;

        $typeOrder = [
            MeterType::ColdWater->value => 0,
            MeterType::HotWater->value => 1,
            MeterType::Electricity->value => 2,
        ];

        $periodReadings = $readings
            ->sortBy(fn (MeterReading $reading): int => $typeOrder[$reading->type->value] ?? 99)
            ->values();

        foreach ($periodReadings as $periodReading) {
            $consumption = $periodReading->consumption !== null
                ? (float) $periodReading->consumption
                : ($consumptionData[$periodReading->id]['consumption'] ?? null);

            if ($consumption === null || $consumption <= 0) {
                continue;
            }

            $unitTariff = $tariffs[$periodReading->type->value] ?? 0;
            $lineAmount = $this->lineAmountForReading($periodReading, $consumption, $tariffs) ?? 0;
            $utilitiesAmount += $lineAmount;

            if ($periodReading->type === MeterType::ColdWater) {
                $coldConsumption += $consumption;
            }

            if ($periodReading->type === MeterType::HotWater) {
                $hotConsumption += $consumption;
            }

            if ($lineAmount > 0) {
                $breakdown[] = [
                    'key' => $periodReading->type->value,
                    'label' => $periodReading->type->label(),
                    'consumption' => $consumption,
                    'unit' => $periodReading->type->consumptionUnit(),
                    'tariff' => $unitTariff,
                    'amount' => $lineAmount,
                ];
            }

            $periodReading->update([
                'consumption' => $consumption,
                'charged_amount' => $lineAmount > 0 ? $lineAmount : null,
            ]);
        }

        $sewageTariff = $tariffs[MeterType::Sewage->value] ?? 0;
        $sewageConsumption = $coldConsumption + $hotConsumption;
        $sewageAmount = 0.0;

        if ($sewageTariff > 0 && $sewageConsumption > 0) {
            $sewageAmount = round($sewageConsumption * $sewageTariff, 2);
            $utilitiesAmount += $sewageAmount;

            $breakdown[] = [
                'key' => MeterType::Sewage->value,
                'label' => MeterType::Sewage->label(),
                'consumption' => $sewageConsumption,
                'unit' => 'м³',
                'tariff' => $sewageTariff,
                'amount' => $sewageAmount,
            ];
        }

        if ($utilitiesAmount <= 0) {
            return null;
        }

        $existingChargeId = MeterReading::query()
            ->where('user_id', $userId)
            ->whereDate('reading_date', $readingDate)
            ->where('is_initial', false)
            ->where('status', MeterReadingStatus::Approved)
            ->whereNotNull('charge_id')
            ->value('charge_id');

        $existingCharge = $existingChargeId !== null
            ? Charge::query()->find($existingChargeId)
            : null;

        $formattedAmount = number_format($utilitiesAmount, 2, '.', '');
        $paymentDueDate = $this->contractBillingService
            ->paymentDueDateForRenter($userId, $readingDate)
            ?->toDateString();

        if ($existingCharge !== null) {
            $existingCharge->update([
                'total_amount' => $formattedAmount,
                'breakdown' => $breakdown,
            ]);
            $charge = $existingCharge;
            $isNewCharge = false;
        } else {
            $charge = Charge::create([
                'user_id' => $userId,
                'category' => ChargeCategory::Utilities,
                'total_amount' => $formattedAmount,
                'breakdown' => $breakdown,
                'paid_amount' => 0,
                'last_payment_date' => $paymentDueDate,
                'status' => ChargeStatus::Unpaid,
            ]);
            $isNewCharge = true;
        }

        MeterReading::query()
            ->whereIn('id', $readings->pluck('id'))
            ->update(['charge_id' => $charge->id]);

        if ($isNewCharge) {
            $readings->first()?->loadMissing('renter');
            $readings->first()?->renter?->notify(new ChargeCreatedNotification($charge));
        }

        return $charge;
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, RoomType>
     */
    private function roomTypesByUserId(Collection $userIds): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->with('room:id,user_id,type')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->room?->type ?? RoomType::Room,
            ])
            ->all();
    }

    private function roomTypeForUser(int $userId): RoomType
    {
        $roomType = User::query()
            ->whereKey($userId)
            ->with('room:id,user_id,type')
            ->first()
            ?->room
            ?->type;

        return $roomType ?? RoomType::Room;
    }

    /**
     * @param  array<string, float>  $tariffs
     */
    private function lineAmountForReading(MeterReading $reading, ?float $consumption, array $tariffs): ?float
    {
        if ($consumption === null || $consumption <= 0) {
            return null;
        }

        $unitTariff = $tariffs[$reading->type->value] ?? 0;

        if ($unitTariff <= 0) {
            return null;
        }

        return round($consumption * $unitTariff, 2);
    }

    private function consumptionForReading(MeterReading $reading): ?float
    {
        $history = MeterReading::query()
            ->forConsumption()
            ->where('user_id', $reading->user_id)
            ->get();

        $result = $this->consumptionService->calculateForReadings(
            collect([$reading->fresh()]),
            $history,
        );

        return $result[$reading->id]['consumption'] ?? null;
    }
}
