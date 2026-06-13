<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['rooms_id', 'number', 'conclusion_date', 'expiration_date', 'payment_terms', 'termination_terms', 'file_path'])]
class Contracts extends Model
{
    use HasFactory;

    protected $casts = [
        'conclusion_date' => 'date:Y-m-d',
        'expiration_date' => 'date:Y-m-d',
    ];

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'rooms_id');
    }

    /**
     * Срок оплаты начисления: тот же день месяца, что и дата заключения договора,
     * но в месяце, следующем за датой начисления. Формат d.m.Y.
     */
    public function dueDateFor(CarbonInterface|string $createDate): string
    {
        $nextMonth = CarbonImmutable::parse($createDate)->addMonthNoOverflow()->startOfMonth();
        $day = min($this->conclusion_date->day, $nextMonth->daysInMonth);

        return $nextMonth->day($day)->format('d.m.Y');
    }

    /**
     * Расчётные периоды показаний: от даты заключения договора
     * до того же числа следующего месяца (включительно).
     *
     * @return list<array{start: CarbonImmutable, end: CarbonImmutable}>
     */
    public function billingPeriods(CarbonInterface|string|null $until = null): array
    {
        $until = $until !== null
            ? CarbonImmutable::parse($until)->startOfDay()
            : CarbonImmutable::now()->startOfDay();

        $expiration = $this->expiration_date->toImmutable()->startOfDay();
        $periods = [];
        $start = $this->conclusion_date->toImmutable()->startOfDay();

        while ($start->lte($expiration) && $start->lte($until)) {
            $end = $this->periodEndFor($start);

            $periods[] = [
                'start' => $start,
                'end' => $end,
            ];

            $nextStart = $this->nextPeriodStart($start);

            if ($nextStart->gt($expiration)) {
                break;
            }

            $start = $nextStart;
        }

        return $periods;
    }

    /**
     * Конец периода: тот же день, что и дата заключения, в следующем месяце.
     */
    public function periodEndFor(CarbonImmutable $periodStart): CarbonImmutable
    {
        return $this->nextPeriodStart($periodStart);
    }

    /**
     * Начало следующего периода: тот же день месяца, что и дата заключения.
     */
    public function nextPeriodStart(CarbonImmutable $currentStart): CarbonImmutable
    {
        $nextMonth = $currentStart->addMonthNoOverflow()->startOfMonth();
        $day = min($this->conclusion_date->day, $nextMonth->daysInMonth);

        return $nextMonth->day($day);
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}|null
     */
    public function findPeriodByStart(CarbonInterface|string $periodStart): ?array
    {
        $target = CarbonImmutable::parse($periodStart)->startOfDay()->format('Y-m-d');

        foreach ($this->billingPeriods($this->expiration_date) as $period) {
            if ($period['start']->format('Y-m-d') === $target) {
                return $period;
            }
        }

        return null;
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->file_path
            ? Storage::disk('public')->url($this->file_path)
            : null);
    }
}
