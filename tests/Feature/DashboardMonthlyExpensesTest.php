<?php

use App\Models\Expense;
use App\Models\User;
use App\Services\DashboardMonthlyExpensesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard monthly expenses default to current month', function () {
    $this->travelTo('2026-06-15');

    Expense::factory()->create([
        'title' => 'Текущий месяц',
        'amount' => 120,
        'created_at' => '2026-06-10 10:00:00',
    ]);

    Expense::factory()->create([
        'title' => 'Прошлый месяц',
        'amount' => 999,
        'created_at' => '2026-05-10 10:00:00',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('monthlyExpenses.month', '2026-06')
            ->where('monthlyExpenses.total_amount', 120)
            ->has('monthlyExpenses.expense_groups', 1)
            ->where('monthlyExpenses.expense_groups.0.expenses.0.title', 'Текущий месяц'));
});

test('dashboard monthly expenses can be filtered by month', function () {
    Expense::factory()->create([
        'title' => 'Июнь',
        'amount' => 100,
        'created_at' => '2026-06-20 10:00:00',
    ]);

    Expense::factory()->create([
        'title' => 'Июль',
        'amount' => 250,
        'created_at' => '2026-07-05 10:00:00',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard', ['expense_month' => '2026-07']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('monthlyExpenses.month', '2026-07')
            ->where('monthlyExpenses.total_amount', 250)
            ->has('monthlyExpenses.expense_groups', 1)
            ->where('monthlyExpenses.expense_groups.0.expenses.0.title', 'Июль'));
});

test('invalid expense month falls back to current month', function () {
    $this->travelTo('2026-06-15');

    $service = app(DashboardMonthlyExpensesService::class);

    expect($service->resolveMonth('invalid')->format('Y-m'))->toBe('2026-06');
    expect($service->resolveMonth('2026-13')->format('Y-m'))->toBe('2026-06');
});

test('renter dashboard does not include monthly expenses', function () {
    Expense::factory()->create();

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('monthlyExpenses', null));
});
