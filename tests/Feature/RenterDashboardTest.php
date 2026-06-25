<?php

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;

test('renter dashboard includes personal cabinet data', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create([
        'last_name' => 'Иванов',
        'name' => 'Иван',
    ]);

    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'number' => '7',
        'floor' => 2,
        'status' => RoomStatus::Occupied,
    ]);

    Contract::factory()->create([
        'user_id' => $renter->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
    ]);

    $currentCharge = Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Utilities,
        'total_amount' => 812.50,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-25',
        'created_at' => '2026-06-01 10:00:00',
        'breakdown' => [
            [
                'key' => 'cold_water',
                'label' => 'Холодная вода',
                'consumption' => 5.2,
                'unit' => 'м³',
                'tariff' => 1.2,
                'amount' => 6.24,
            ],
        ],
    ]);

    $previousCharge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 805.20,
        'paid_amount' => 805.20,
        'status' => ChargeStatus::Paid,
        'created_at' => '2026-05-01 10:00:00',
    ]);

    Payment::factory()->approved()->create([
        'charge_id' => $previousCharge->id,
        'amount' => 805.20,
        'created_at' => '2026-06-01 12:00:00',
    ]);

    adminUser()->news()->create([
        'title' => 'Плановые работы',
        'text' => 'Техобслуживание лифта',
        'date' => '2026-06-15',
    ]);

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('statistics', null)
            ->where('renterDashboard.summary.due_amount', 0)
            ->where('renterDashboard.summary.debt_amount', 0)
            ->where('renterDashboard.summary.last_payment.amount', 805.2)
            ->where('renterDashboard.summary.room.number', $room->number)
            ->has('renterDashboard.monthly_charges.charges', 1)
            ->where('renterDashboard.monthly_charges.charges.0.display_status', 'unpaid')
            ->where('renterDashboard.monthly_charges.total_to_pay', 0)
            ->has('renterDashboard.payment_history', 1)
            ->has('renterDashboard.news', 1)
            ->where('renterDashboard.summary.pay_charge', null)
            ->where('renterDashboard.summary.next_charge.date', '01.07.2026')
            ->where('renterDashboard.summary.next_charge.days_until', 11)
            ->where('renterDashboard.summary.has_contract', true));
});

test('next charge is always in the next calendar month', function () {
    $this->travelTo('2026-06-05');

    $renter = User::factory()->renter()->create();

    Contract::factory()->create([
        'user_id' => $renter->id,
        'start_date' => '2026-01-15',
        'end_date' => null,
    ]);

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('renterDashboard.summary.next_charge.date', '15.07.2026')
            ->where('renterDashboard.summary.next_charge.days_until', 40)
            ->where('renterDashboard.summary.has_contract', true));
});

test('next charge uses first billing date when contract starts after next month', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();

    Contract::factory()->create([
        'user_id' => $renter->id,
        'start_date' => '2026-08-15',
        'end_date' => null,
    ]);

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('renterDashboard.summary.has_contract', true)
            ->where('renterDashboard.summary.next_charge.date', '15.08.2026')
            ->where('renterDashboard.summary.next_charge.days_until', 56));
});

test('due amount includes only overdue charges', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 100,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-25',
        'created_at' => '2026-06-01 10:00:00',
    ]);

    $overdueCharge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 200,
        'paid_amount' => 0,
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-15',
        'created_at' => '2026-06-01 11:00:00',
    ]);

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('renterDashboard.summary.due_amount', 200)
            ->where('renterDashboard.summary.debt_amount', 200)
            ->where('renterDashboard.summary.pay_charge.id', $overdueCharge->id)
            ->has('renterDashboard.monthly_charges.charges', 2)
            ->where('renterDashboard.monthly_charges.charges.0.display_status', 'debt')
            ->where('renterDashboard.monthly_charges.charges.1.display_status', 'unpaid')
            ->where('renterDashboard.monthly_charges.total_to_pay', 200));
});

test('overdue unpaid charge counts as due and debt amount', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 150,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-15',
    ]);

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('renterDashboard.summary.due_amount', 150)
            ->where('renterDashboard.summary.debt_amount', 150)
            ->where('renterDashboard.summary.pay_charge.id', $charge->id));
});

test('renter dashboard does not include admin statistics', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('statistics', null)
            ->where('housePlan', null)
            ->where('monthlyExpenses', null)
            ->where('recentPayments', null)
            ->where('recentMeterReadings', null)
            ->where('rentersWithDebt', null)
            ->where('financialChart', null)
            ->where('dashboardNotifications', null)
            ->where('dashboardNews', null)
            ->has('renterDashboard'));
});
