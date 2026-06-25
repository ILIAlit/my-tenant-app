<?php

use App\Enums\ChargeStatus;
use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ChargePaymentDueReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('command sends reminder three days before payment due date', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-23',
    ]);

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 1');

    Notification::assertSentTo($renter, ChargePaymentDueReminderNotification::class);
});

test('command skips charges due on other dates', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-25',
    ]);

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 0');

    Notification::assertNothingSentTo($renter);
});

test('command skips paid charges', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 500,
        'status' => ChargeStatus::Paid,
        'last_payment_date' => '2026-06-23',
    ]);

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 0');

    Notification::assertNothingSentTo($renter);
});

test('command skips charges without remaining amount', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Pending,
        'last_payment_date' => '2026-06-23',
    ]);

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 500,
        'status' => PaymentStatus::Pending,
    ]);

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 0');

    Notification::assertNothingSentTo($renter);
});

test('command does not send duplicate reminders for same charge', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-23',
    ]);

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 1');

    $this->artisan('app:send-charge-payment-reminders', ['--date' => '2026-06-20'])
        ->assertSuccessful()
        ->expectsOutputToContain('Отправлено напоминаний: 0');

    expect($renter->fresh()->notifications)->toHaveCount(1);
});
