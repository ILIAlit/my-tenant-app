<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardMonthlyExpensesService
{
    public function resolveMonth(?string $month): CarbonInterface
    {
        if ($month === null || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return now()->startOfMonth();
        }

        return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    }

    /**
     * @return array{
     *     month: string,
     *     total_amount: float,
     *     expense_groups: list<array{
     *         date: string,
     *         total_amount: float,
     *         expenses: list<array{
     *             id: int,
     *             title: string,
     *             amount: float,
     *             description: string|null,
     *             created_at: string,
     *         }>,
     *     }>,
     * }
     */
    public function getForMonth(CarbonInterface $month): array
    {
        $expenses = Expense::query()
            ->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderByDesc('created_at')
            ->get();

        /** @var Collection<string, Collection<int, Expense>> $grouped */
        $grouped = $expenses->groupBy(
            fn (Expense $expense): string => $expense->created_at->format('Y-m-d'),
        );

        $expenseGroups = $grouped
            ->map(function (Collection $dayExpenses, string $date): array {
                return [
                    'date' => $date,
                    'total_amount' => round($dayExpenses->sum(
                        fn (Expense $expense): float => (float) $expense->amount,
                    ), 2),
                    'expenses' => $dayExpenses->map(fn (Expense $expense): array => [
                        'id' => $expense->id,
                        'title' => $expense->title,
                        'amount' => (float) $expense->amount,
                        'description' => $expense->description,
                        'created_at' => $expense->created_at->format('Y-m-d H:i'),
                    ])->values()->all(),
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->all();

        return [
            'month' => $month->format('Y-m'),
            'total_amount' => round($expenses->sum(
                fn (Expense $expense): float => (float) $expense->amount,
            ), 2),
            'expense_groups' => $expenseGroups,
        ];
    }
}
