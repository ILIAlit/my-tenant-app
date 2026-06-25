<?php

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\RenterService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createRenterWithContract(array $contractOverrides = []): User
{
    $renter = User::factory()->renter()->create();

    Contract::factory()->create(array_merge([
        'user_id' => $renter->id,
        'start_date' => '2026-01-16',
        'end_date' => null,
        'monthly_rent' => 400,
    ], $contractOverrides));

    return $renter->fresh(['contract', 'renterServices']);
}

test('command creates charge on contract billing day', function () {
    $renter = createRenterWithContract();

    RenterService::factory()->create([
        'user_id' => $renter->id,
        'price' => 25.50,
        'is_active' => true,
    ]);

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 1');

    $charge = Charge::query()->where('user_id', $renter->id)->first();

    expect($charge)->not->toBeNull();
    expect((float) $charge->total_amount)->toBe(425.50);
    expect((float) $charge->paid_amount)->toBe(0.0);
    expect($charge->status)->toBe(ChargeStatus::Unpaid);
    expect($charge->category)->toBe(ChargeCategory::Rent);
    expect($charge->last_payment_date?->format('Y-m-d'))->toBe('2026-07-16');
    expect($charge->created_at->format('Y-m-d'))->toBe('2026-06-16');
});

test('command skips renters when today is not billing day', function () {
    createRenterWithContract();

    $this->artisan('app:create-charges', ['--date' => '2026-06-15'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 0');

    expect(Charge::query()->count())->toBe(0);
});

test('command excludes inactive services from total', function () {
    $renter = createRenterWithContract();

    RenterService::factory()->create([
        'user_id' => $renter->id,
        'price' => 30,
        'is_active' => true,
    ]);

    RenterService::factory()->create([
        'user_id' => $renter->id,
        'price' => 50,
        'is_active' => false,
    ]);

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful();

    expect((float) Charge::query()->first()->total_amount)->toBe(430.0);
});

test('command does not create duplicate charge for same month', function () {
    $renter = createRenterWithContract();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'created_at' => Carbon::parse('2026-06-16'),
        'updated_at' => Carbon::parse('2026-06-16'),
    ]);

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 0');

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(1);
});

test('command skips renters without contract', function () {
    User::factory()->renter()->create();

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 0');
});

test('command skips charge before contract start date', function () {
    createRenterWithContract(['start_date' => '2026-06-20']);

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 0');
});

test('command skips charge after contract end date', function () {
    createRenterWithContract([
        'start_date' => '2026-01-16',
        'end_date' => '2026-05-31',
    ]);

    $this->artisan('app:create-charges', ['--date' => '2026-06-16'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 0');
});

test('command uses last day of month when billing day exceeds month length', function () {
    $renter = createRenterWithContract(['start_date' => '2026-01-31', 'monthly_rent' => 300]);

    $this->artisan('app:create-charges', ['--date' => '2026-04-30'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 1');

    $charge = Charge::query()->where('user_id', $renter->id)->first();

    expect($charge->created_at->format('Y-m-d'))->toBe('2026-04-30');
    expect($charge->last_payment_date?->format('Y-m-d'))->toBe('2026-05-31');
    expect((float) $charge->total_amount)->toBe(300.0);
});

test('each renter uses own contract billing day', function () {
    $firstRenter = createRenterWithContract(['start_date' => '2026-01-10']);
    $secondRenter = createRenterWithContract(['start_date' => '2026-02-16']);

    $this->artisan('app:create-charges', ['--date' => '2026-06-10'])
        ->assertSuccessful()
        ->expectsOutputToContain('Создано начислений: 1');

    expect(Charge::query()->where('user_id', $firstRenter->id)->exists())->toBeTrue();
    expect(Charge::query()->where('user_id', $secondRenter->id)->exists())->toBeFalse();
});
