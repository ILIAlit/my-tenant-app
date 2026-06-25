<?php

use App\Enums\ChargeCategory;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard includes financial chart for current month by default', function () {
    $this->travelTo('2026-06-15');

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Rent,
        'total_amount' => 400,
        'created_at' => '2026-06-10 10:00:00',
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Utilities,
        'total_amount' => 100,
        'created_at' => '2026-06-12 10:00:00',
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Rent,
        'total_amount' => 999,
        'created_at' => '2026-05-10 10:00:00',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('financialChart.month', '2026-06')
            ->where('financialChart.total_amount', 500)
            ->has('financialChart.categories', 2)
            ->where('financialChart.categories.0.category', 'rent')
            ->where('financialChart.categories.0.amount', 400)
            ->where('financialChart.categories.0.percentage', 80)
            ->where('financialChart.categories.1.category', 'utilities')
            ->where('financialChart.categories.1.amount', 100));
});

test('financial chart can be filtered by month', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Rent,
        'total_amount' => 300,
        'created_at' => '2026-07-01 10:00:00',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard', ['finance_month' => '2026-07']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('financialChart.month', '2026-07')
            ->where('financialChart.total_amount', 300)
            ->has('financialChart.categories', 1));
});

test('renter dashboard does not include financial chart', function () {
    Charge::factory()->create();

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('financialChart', null));
});
