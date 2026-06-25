<?php

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\MeterReading;
use App\Models\MeterTariff;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function setMeterTariffs(): void
{
    MeterTariff::query()->where('type', MeterType::ColdWater)->update(['price_per_unit' => 2]);
    MeterTariff::query()->where('type', MeterType::HotWater)->update(['price_per_unit' => 4]);
    MeterTariff::query()->where('type', MeterType::Electricity)->update(['price_per_unit' => 0.5]);
    MeterTariff::query()->where('type', MeterType::Sewage)->update(['price_per_unit' => 8]);
}

function createBillingRenterWithContract(): User
{
    $renter = User::factory()->renter()->create();

    Contract::factory()->create([
        'user_id' => $renter->id,
        'start_date' => '2026-01-08',
    ]);

    return $renter;
}

function createInitialReading(User $renter, MeterType $type, float $value): void
{
    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => $type,
        'reading_date' => '2026-01-01',
        'value' => $value,
        'is_initial' => true,
        'status' => MeterReadingStatus::Approved,
    ]);
}

test('approving readings on same date creates single utilities charge', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();
    createInitialReading($renter, MeterType::ColdWater, 100);
    createInitialReading($renter, MeterType::HotWater, 10);
    createInitialReading($renter, MeterType::Electricity, 1000);

    $coldReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 115,
        'status' => MeterReadingStatus::Pending,
    ]);

    $hotReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-01',
        'value' => 15,
        'status' => MeterReadingStatus::Pending,
    ]);

    $electricityReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'reading_date' => '2026-06-01',
        'value' => 1100,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $coldReading->id))
        ->assertRedirect(route('meter-readings.get'));

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $hotReading->id))
        ->assertRedirect(route('meter-readings.get'));

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $electricityReading->id))
        ->assertRedirect(route('meter-readings.get'));

    $coldReading->refresh();
    $hotReading->refresh();
    $electricityReading->refresh();

    expect((float) $coldReading->consumption)->toBe(15.0);
    expect((float) $hotReading->consumption)->toBe(5.0);
    expect((float) $electricityReading->consumption)->toBe(100.0);

    expect($coldReading->charge_id)->not->toBeNull();
    expect($hotReading->charge_id)->toBe($coldReading->charge_id);
    expect($electricityReading->charge_id)->toBe($coldReading->charge_id);

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(1);

    $this->assertDatabaseHas('charges', [
        'id' => $coldReading->charge_id,
        'user_id' => $renter->id,
        'category' => ChargeCategory::Utilities->value,
        'total_amount' => 260,
        'status' => ChargeStatus::Unpaid->value,
    ]);

    expect(Charge::query()->find($coldReading->charge_id)->last_payment_date?->format('Y-m-d'))
        ->toBe('2026-07-08');

    $charge = Charge::query()->find($coldReading->charge_id);
    expect($charge->breakdown)->toHaveCount(4);
    expect($charge->breakdown[0])->toMatchArray([
        'key' => 'cold_water',
        'label' => 'Холодная вода',
        'amount' => 30.0,
    ]);
    expect($charge->breakdown[3])->toMatchArray([
        'key' => 'sewage',
        'label' => 'Канализация',
        'consumption' => 20.0,
        'amount' => 160.0,
    ]);
});

test('utilities charge is recalculated when additional reading is approved', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();
    createInitialReading($renter, MeterType::ColdWater, 100);
    createInitialReading($renter, MeterType::HotWater, 10);

    $coldReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 115,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $coldReading->id));

    $coldReading->refresh();

    expect(Charge::query()->where('user_id', $renter->id)->value('total_amount'))->toBe('150.00');

    $hotReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-01',
        'value' => 15,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $hotReading->id));

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(1);
    expect(Charge::query()->where('user_id', $renter->id)->value('total_amount'))->toBe('210.00');
    expect($hotReading->fresh()->charge_id)->toBe($coldReading->charge_id);
});

test('different reading dates create separate utilities charges', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();
    createInitialReading($renter, MeterType::ColdWater, 100);

    $firstReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 115,
        'status' => MeterReadingStatus::Pending,
    ]);

    $secondReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-07-01',
        'value' => 120,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())->put(route('meter-readings.approve', $firstReading->id));
    $this->actingAs(adminUser())->put(route('meter-readings.approve', $secondReading->id));

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(2);
    expect($firstReading->fresh()->charge_id)->not->toBe($secondReading->fresh()->charge_id);
});

test('approving reading without consumption does not create charge', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'reading_date' => '2026-06-01',
        'value' => 1000,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    expect($reading->fresh()->charge_id)->toBeNull();
    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(0);
});

test('approving already processed reading does not create duplicate charge', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();
    createInitialReading($renter, MeterType::HotWater, 10);

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-01',
        'value' => 20,
        'status' => MeterReadingStatus::Approved,
        'consumption' => 10,
        'charged_amount' => 40,
        'charge_id' => Charge::factory()->create([
            'user_id' => $renter->id,
            'category' => ChargeCategory::Utilities,
            'total_amount' => 40,
        ])->id,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $reading->id))
        ->assertSessionHasErrors('id');

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(1);
});

test('garage renter is billed with garage tariffs', function () {
    Notification::fake();

    MeterTariff::query()->where('room_type', RoomType::Room)->where('type', MeterType::ColdWater)->update(['price_per_unit' => 2]);
    MeterTariff::query()->where('room_type', RoomType::Garage)->where('type', MeterType::ColdWater)->update(['price_per_unit' => 1]);

    $renter = createBillingRenterWithContract();
    Room::factory()->garage()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    createInitialReading($renter, MeterType::ColdWater, 100);

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 115,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    expect(Charge::query()->where('user_id', $renter->id)->value('total_amount'))->toBe('15.00');
});

test('rejecting meter reading does not create charge', function () {
    Notification::fake();
    setMeterTariffs();

    $renter = createBillingRenterWithContract();
    createInitialReading($renter, MeterType::ColdWater, 50);

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'value' => 70,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.reject', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(0);
});
