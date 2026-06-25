<?php

namespace App\Services;

use App\Enums\ChargeCategory;
use App\Models\Charge;
use Carbon\CarbonInterface;

class DashboardFinancialChartService
{
    /**
     * @return array{
     *     month: string,
     *     total_amount: float,
     *     categories: list<array{
     *         category: string,
     *         label: string,
     *         amount: float,
     *         percentage: float,
     *     }>,
     * }
     */
    public function getForMonth(CarbonInterface $month): array
    {
        $totals = Charge::query()
            ->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->selectRaw('category, COALESCE(SUM(total_amount), 0) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = collect(ChargeCategory::cases())
            ->map(function (ChargeCategory $category) use ($totals): array {
                $amount = round((float) ($totals[$category->value] ?? 0), 2);

                return [
                    'category' => $category->value,
                    'label' => $category->label(),
                    'amount' => $amount,
                ];
            })
            ->filter(fn (array $item): bool => $item['amount'] > 0)
            ->sortByDesc('amount')
            ->values();

        $totalAmount = round($categories->sum('amount'), 2);

        $categoriesWithPercentage = $categories
            ->map(function (array $item) use ($totalAmount): array {
                $item['percentage'] = $totalAmount > 0
                    ? round(($item['amount'] / $totalAmount) * 100, 1)
                    : 0.0;

                return $item;
            })
            ->all();

        return [
            'month' => $month->format('Y-m'),
            'total_amount' => $totalAmount,
            'categories' => $categoriesWithPercentage,
        ];
    }
}
