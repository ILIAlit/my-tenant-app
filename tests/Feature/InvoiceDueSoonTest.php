<?php

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\Payments;
use App\Models\Rooms;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-06-13');

    $this->renter = User::factory()->create([
        'login' => 'renter1',
        'role' => UserRole::RENTER->value,
    ]);

    $this->room = Rooms::create([
        'user_id' => $this->renter->id,
        'number' => 101,
        'floor' => 1,
        'square' => 20,
        'status' => 'used',
    ]);

    $this->contract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-101',
        'conclusion_date' => '2026-01-15',
        'expiration_date' => '2027-01-15',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function makeInvoice(array $overrides = []): Invoices
{
    return Invoices::create(array_merge([
        'user_id' => test()->renter->id,
        'rooms_id' => test()->room->id,
        'contracts_id' => test()->contract->id,
        'period_start' => '2026-05-16',
        'name' => 'Начисление за комнату № 101',
        'total_price' => 15000,
        'paid_price' => 0,
        'create_date' => '16.05.2026',
        'due_date' => '16.06.2026',
    ], $overrides));
}

test('renter is notified three days before the due date', function () {
    makeInvoice();

    $this->artisan('invoices:notify-due-soon')->assertSuccessful();

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('invoice_due_soon')
        ->and($notification->data['days_left'])->toBe(3)
        ->and($notification->data['amount'])->toBe(15000)
        ->and($notification->data['due_date'])->toBe('16.06.2026');
});

test('no notification when due date is not in three days', function () {
    makeInvoice(['due_date' => '20.06.2026']);

    $this->artisan('invoices:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});

test('paid invoices do not trigger a reminder', function () {
    makeInvoice(['paid_price' => 15000]);

    $this->artisan('invoices:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});

test('invoices with a payment under review do not trigger a reminder', function () {
    $invoice = makeInvoice();

    Payments::create([
        'invoices_id' => $invoice->id,
        'amount' => 5000,
        'status' => PaymentStatus::Review->value,
    ]);

    $this->artisan('invoices:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});

test('reminder is not sent twice for the same invoice', function () {
    makeInvoice();

    $this->artisan('invoices:notify-due-soon')->assertSuccessful();
    $this->artisan('invoices:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(1);
});

test('custom days option changes the reminder window', function () {
    makeInvoice(['due_date' => '18.06.2026']);

    $this->artisan('invoices:notify-due-soon', ['--days' => 5])->assertSuccessful();

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['days_left'])->toBe(5);
});
