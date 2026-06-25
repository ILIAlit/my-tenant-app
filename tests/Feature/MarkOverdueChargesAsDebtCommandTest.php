<?php

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('command marks overdue unpaid charges as debt', function () {
    $renter = User::factory()->renter()->create();

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-15',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Переведено в долг: 1');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Debt);
});

test('command marks charge as debt on due date', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 300,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-20',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Переведено в долг: 1');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Debt);
});

test('command skips charges before due date', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-25',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Переведено в долг: 0');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Unpaid);
});

test('command skips paid charges', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 500,
        'status' => ChargeStatus::Paid,
        'last_payment_date' => '2026-06-15',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Переведено в долг: 0');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Paid);
});

test('command skips charges already in debt status', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-15',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Переведено в долг: 0');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Debt);
});

test('command reverts legacy debt charges before due date to unpaid', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-25',
    ]);

    $this->artisan('app:mark-overdue-charges-as-debt', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('возвращено в ожидание: 1');

    expect($charge->fresh()->status)->toBe(ChargeStatus::Unpaid);
});
