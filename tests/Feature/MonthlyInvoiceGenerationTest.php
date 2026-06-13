<?php

use App\Actions\Invoices\MonthlyInvoiceGenerator;
use App\Enums\UserRole;
use App\Models\Amenities;
use App\Models\Contracts;
use App\Models\Invoices;
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
        'conclusion_date' => '2026-06-03',
        'expiration_date' => '2027-06-03',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    Amenities::create([
        'rooms_id' => $this->room->id,
        'name' => 'Аренда',
        'price' => 15000,
    ]);

    Amenities::create([
        'rooms_id' => $this->room->id,
        'name' => 'Коммунальные услуги',
        'price' => 2500,
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('monthly invoice generator creates invoice for started billing period', function () {
    $generator = app(MonthlyInvoiceGenerator::class);

    $invoice = $generator->createForCurrentPeriod($this->room);

    expect($invoice)->not->toBeNull()
        ->and($invoice->user_id)->toBe($this->renter->id)
        ->and($invoice->rooms_id)->toBe($this->room->id)
        ->and($invoice->contracts_id)->toBe($this->contract->id)
        ->and($invoice->period_start->format('Y-m-d'))->toBe('2026-06-03')
        ->and($invoice->total_price)->toBe(17500)
        ->and($invoice->due_date)->toBe('03.07.2026')
        ->and($invoice->name)->toContain('03.06.2026 — 03.07.2026');
});

test('monthly invoice generator skips duplicate invoices for the same period', function () {
    $generator = app(MonthlyInvoiceGenerator::class);

    $generator->createForCurrentPeriod($this->room);
    $duplicate = $generator->createForCurrentPeriod($this->room);

    expect($duplicate)->toBeNull()
        ->and(Invoices::query()->where('rooms_id', $this->room->id)->count())->toBe(1);
});

test('generate command creates invoices for all started periods', function () {
    $this->artisan('invoices:generate')
        ->assertSuccessful();

    expect(Invoices::query()->where('rooms_id', $this->room->id)->count())->toBe(1);

    $invoice = Invoices::query()->where('rooms_id', $this->room->id)->first();

    expect($invoice->period_start->format('Y-m-d'))->toBe('2026-06-03')
        ->and($invoice->total_price)->toBe(17500);
});

test('generate command creates invoice when new billing period starts', function () {
    app(MonthlyInvoiceGenerator::class)->createForCurrentPeriod($this->room);

    CarbonImmutable::setTestNow('2026-07-03');

    $this->artisan('invoices:generate')
        ->assertSuccessful();

    $invoices = Invoices::query()
        ->where('rooms_id', $this->room->id)
        ->orderBy('period_start')
        ->get();

    expect($invoices)->toHaveCount(2)
        ->and($invoices[0]->period_start->format('Y-m-d'))->toBe('2026-06-03')
        ->and($invoices[1]->period_start->format('Y-m-d'))->toBe('2026-07-03')
        ->and($invoices[1]->due_date)->toBe('03.08.2026');
});

test('generate command dry run does not create invoices', function () {
    $this->artisan('invoices:generate --dry-run')
        ->assertSuccessful();

    expect(Invoices::count())->toBe(0);
});

test('generate command skips rooms without contract', function () {
    $roomWithoutContract = Rooms::create([
        'user_id' => $this->renter->id,
        'number' => 202,
        'floor' => 2,
        'square' => 18,
        'status' => 'used',
    ]);

    Amenities::create([
        'rooms_id' => $roomWithoutContract->id,
        'name' => 'Аренда',
        'price' => 10000,
    ]);

    $this->artisan('invoices:generate')
        ->assertSuccessful();

    expect(Invoices::query()->where('rooms_id', $roomWithoutContract->id)->exists())->toBeFalse();
});

test('assigning renter to room creates invoice for current billing period', function () {
    $newRenter = User::factory()->create([
        'login' => 'renter2',
        'role' => UserRole::RENTER->value,
    ]);

    $room = Rooms::create([
        'number' => 303,
        'floor' => 3,
        'square' => 22,
        'status' => 'free',
    ]);

    Contracts::create([
        'rooms_id' => $room->id,
        'number' => 'Д-303',
        'conclusion_date' => '2026-06-03',
        'expiration_date' => '2027-06-03',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    Amenities::create([
        'rooms_id' => $room->id,
        'name' => 'Аренда',
        'price' => 12000,
    ]);

    $room->update(['user_id' => $newRenter->id]);

    $invoice = Invoices::query()->where('rooms_id', $room->id)->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->user_id)->toBe($newRenter->id)
        ->and($invoice->period_start->format('Y-m-d'))->toBe('2026-06-03');
});

test('invoices generate command is scheduled daily', function () {
    $this->artisan('schedule:list')
        ->assertSuccessful()
        ->expectsOutputToContain('invoices:generate');
});
