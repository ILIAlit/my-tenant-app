<?php

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard includes renters with overdue debt', function () {
    $this->travelTo('2026-06-20');

    $renterWithDebt = User::factory()->renter()->create([
        'last_name' => 'Иванов',
        'name' => 'Иван',
        'middle_name' => null,
    ]);

    $renterWithoutDebt = User::factory()->renter()->create([
        'last_name' => 'Петров',
        'name' => 'Пётр',
    ]);

    Room::factory()->create([
        'user_id' => $renterWithDebt->id,
        'number' => '101',
        'floor' => 1,
    ]);

    Charge::factory()->create([
        'user_id' => $renterWithDebt->id,
        'total_amount' => 500,
        'paid_amount' => 200,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    Charge::factory()->create([
        'user_id' => $renterWithDebt->id,
        'total_amount' => 100,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-10',
        'status' => ChargeStatus::Debt,
    ]);

    Charge::factory()->create([
        'user_id' => $renterWithoutDebt->id,
        'total_amount' => 300,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-25',
        'status' => ChargeStatus::Debt,
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rentersWithDebt', 1)
            ->where('rentersWithDebt.0.id', $renterWithDebt->id)
            ->where('rentersWithDebt.0.debt_amount', 400)
            ->where('rentersWithDebt.0.room.number', '101')
            ->where('rentersWithDebt.0.full_name', 'Иванов Иван'));
});

test('archived charges are excluded from renters with debt', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    Charge::factory()->create([
        'user_id' => null,
        'total_amount' => 1000,
        'paid_amount' => 500,
        'last_payment_date' => '2026-06-01',
        'status' => ChargeStatus::Archived,
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rentersWithDebt', 1)
            ->where('rentersWithDebt.0.id', $renter->id)
            ->where('rentersWithDebt.0.debt_amount', 500));
});

test('renter dashboard does not include renters with debt list', function () {
    $this->travelTo('2026-06-20');

    Charge::factory()->create([
        'user_id' => User::factory()->renter()->create()->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('rentersWithDebt', null));
});
