<?php

namespace App\Actions\Invoices;

use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\Rooms;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MonthlyInvoiceGenerator
{
    /**
     * @return array{created: int, skipped: int}
     */
    public function generate(CarbonInterface|string|null $asOf = null, bool $dryRun = false): array
    {
        $asOf = CarbonImmutable::parse($asOf ?? now())->startOfDay();
        $created = 0;
        $skipped = 0;

        $rooms = Rooms::query()
            ->whereNotNull('user_id')
            ->with([
                'amenities:id,price,rooms_id',
                'contracts' => fn ($query) => $query->latest('conclusion_date'),
            ])
            ->get();

        foreach ($rooms as $room) {
            $contract = $room->contracts->first();

            if ($contract === null) {
                $skipped++;

                continue;
            }

            foreach ($contract->billingPeriods($asOf) as $period) {
                if ($period['start']->gt($asOf)) {
                    continue;
                }

                if ($this->invoiceExists($room->id, $period['start'])) {
                    $skipped++;

                    continue;
                }

                if (! $dryRun) {
                    $this->createForPeriod($room, $contract, $period);
                }

                $created++;
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    public function createForCurrentPeriod(Rooms $room): ?Invoices
    {
        if ($room->user_id === null) {
            return null;
        }

        $room->loadMissing([
            'amenities:id,price,rooms_id',
            'contracts' => fn ($query) => $query->latest('conclusion_date'),
        ]);

        $contract = $room->contracts->first();

        if ($contract === null) {
            return null;
        }

        $periods = $contract->billingPeriods();
        $currentPeriod = Collection::make($periods)->last();

        if ($currentPeriod === null) {
            return null;
        }

        if ($this->invoiceExists($room->id, $currentPeriod['start'])) {
            return null;
        }

        return $this->createForPeriod($room, $contract, $currentPeriod);
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $period
     */
    public function createForPeriod(Rooms $room, Contracts $contract, array $period): Invoices
    {
        $createDate = $period['start'];
        $periodStart = $createDate->format('Y-m-d');

        return Invoices::create([
            'user_id' => $room->user_id,
            'rooms_id' => $room->id,
            'contracts_id' => $contract->id,
            'period_start' => $periodStart,
            'name' => $this->invoiceName($room->number, $period),
            'total_price' => (int) $room->amenities->sum('price'),
            'create_date' => $createDate->format('d.m.Y'),
            'due_date' => $contract->dueDateFor($createDate),
        ]);
    }

    private function invoiceExists(int $roomId, CarbonImmutable $periodStart): bool
    {
        return Invoices::query()
            ->where('rooms_id', $roomId)
            ->whereDate('period_start', $periodStart)
            ->exists();
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $period
     */
    private function invoiceName(int $roomNumber, array $period): string
    {
        $periodLabel = $period['start']->format('d.m.Y')
            .' — '
            .$period['end']->format('d.m.Y');

        return 'Начисление за комнату № '.$roomNumber.' ('.$periodLabel.')';
    }
}
