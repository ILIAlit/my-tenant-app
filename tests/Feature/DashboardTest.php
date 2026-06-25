<?php

use App\Enums\ChargeStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('admin dashboard includes statistics', function () {
    $this->travelTo('2026-06-20');

    $paidCharge = Charge::factory()->create([
        'total_amount' => 100,
        'paid_amount' => 100,
        'status' => ChargeStatus::Paid,
        'last_payment_date' => '2026-06-01',
    ]);

    Payment::factory()->approved()->create([
        'charge_id' => $paidCharge->id,
        'amount' => 100,
    ]);

    Payment::factory()->create([
        'charge_id' => $paidCharge->id,
        'amount' => 50,
        'status' => PaymentStatus::Pending,
    ]);

    Expense::factory()->create(['amount' => 30]);

    Charge::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 200,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    Charge::factory()->create([
        'total_amount' => 300,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-25',
        'status' => ChargeStatus::Debt,
    ]);

    Room::factory()->create(['status' => RoomStatus::Free]);
    Room::factory()->create(['status' => RoomStatus::Occupied]);
    Room::factory()->create(['status' => RoomStatus::Free]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('statistics.total_income', 100)
            ->where('statistics.total_expenses', 30)
            ->where('statistics.total_debts', 600)
            ->where('statistics.free_rooms_count', 2)
            ->where('statistics.net_profit', 70)
            ->where('statistics.debtors_count', 2)
            ->where('statistics.occupied_rooms_count', 1)
            ->where('statistics.paid_rooms_count', 1));
});
