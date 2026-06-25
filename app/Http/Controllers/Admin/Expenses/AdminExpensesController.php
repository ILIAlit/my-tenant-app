<?php

namespace App\Http\Controllers\Admin\Expenses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ExpenseCreateRequest;
use App\Http\Requests\Expense\ExpenseDestroyRequest;
use App\Http\Requests\Expense\ExpenseUpdateRequest;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminExpensesController extends Controller
{
    public function index(): Response
    {
        $groups = Expense::query()
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (Expense $expense): string => $expense->created_at->format('Y-m-d'))
            ->map(function ($expenses, string $date): array {
                return [
                    'date' => $date,
                    'total_amount' => round($expenses->sum(fn (Expense $expense): float => (float) $expense->amount), 2),
                    'expenses' => $expenses->map(fn (Expense $expense): array => $this->formatExpense($expense))->values()->all(),
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->all();

        return Inertia::render('admin/expenses', [
            'expenseGroups' => $groups,
        ]);
    }

    public function store(ExpenseCreateRequest $request): RedirectResponse
    {
        Expense::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Расход создан.')]);

        return to_route('expenses.get');
    }

    public function update(ExpenseUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Expense::query()
            ->findOrFail($validated['id'])
            ->update(collect($validated)->except('id')->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Расход обновлён.')]);

        return to_route('expenses.get');
    }

    public function destroy(ExpenseDestroyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Expense::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Расход удалён.')]);

        return to_route('expenses.get');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatExpense(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'title' => $expense->title,
            'amount' => (float) $expense->amount,
            'description' => $expense->description,
            'created_at' => $expense->created_at->format('Y-m-d H:i'),
        ];
    }
}
