<?php

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function expensePayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Ремонт',
        'amount' => 150.50,
        'description' => 'Мелкий ремонт',
    ], $overrides);
}

test('guest cannot access expenses page', function () {
    $this->get(route('expenses.get'))->assertRedirect(route('login'));
});

test('renter cannot access expenses page', function () {
    $this->actingAs(User::factory()->renter()->create())
        ->get(route('expenses.get'))
        ->assertForbidden();
});

test('admin can view expenses grouped by creation date', function () {
    Expense::factory()->create([
        'title' => 'Расход 1',
        'amount' => 100,
        'created_at' => '2026-06-20 10:00:00',
    ]);

    Expense::factory()->create([
        'title' => 'Расход 2',
        'amount' => 50,
        'created_at' => '2026-06-20 14:00:00',
    ]);

    Expense::factory()->create([
        'title' => 'Расход 3',
        'amount' => 200,
        'created_at' => '2026-06-19 09:00:00',
    ]);

    $this->actingAs(adminUser())
        ->get(route('expenses.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/expenses')
            ->has('expenseGroups', 2)
            ->where('expenseGroups.0.date', '2026-06-20')
            ->where('expenseGroups.0.total_amount', 150)
            ->has('expenseGroups.0.expenses', 2)
            ->where('expenseGroups.1.date', '2026-06-19')
            ->where('expenseGroups.1.total_amount', 200));
});

test('admin can create expense', function () {
    $this->actingAs(adminUser())
        ->post(route('expenses.create'), expensePayload([
            'title' => 'Закупка материалов',
            'amount' => 320,
        ]))
        ->assertRedirect(route('expenses.get'));

    $this->assertDatabaseHas('expenses', [
        'title' => 'Закупка материалов',
        'amount' => 320,
    ]);
});

test('admin can update expense', function () {
    $expense = Expense::factory()->create([
        'title' => 'Старое название',
        'amount' => 100,
    ]);

    $this->actingAs(adminUser())
        ->put(route('expenses.update', $expense->id), expensePayload([
            'title' => 'Новое название',
            'amount' => 250,
            'description' => null,
        ]))
        ->assertRedirect(route('expenses.get'));

    expect($expense->fresh()->title)->toBe('Новое название');
    expect((float) $expense->fresh()->amount)->toBe(250.0);
});

test('admin can delete expense', function () {
    $expense = Expense::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('expenses.delete', $expense->id))
        ->assertRedirect(route('expenses.get'));

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('expense requires valid amount', function () {
    $this->actingAs(adminUser())
        ->post(route('expenses.create'), expensePayload([
            'amount' => 0,
        ]))
        ->assertSessionHasErrors('amount');
});
