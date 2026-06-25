<?php

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function chargePayload(array $overrides = []): array
{
    return array_merge([
        'user_id' => User::factory()->renter()->create()->id,
        'total_amount' => 500,
        'paid_amount' => 250,
        'last_payment_date' => '2026-06-01',
        'status' => ChargeStatus::Pending->value,
    ], $overrides);
}

test('guest cannot access charges page', function () {
    $this->get(route('charges.get'))->assertRedirect(route('login'));
});

test('renter cannot access charges page', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('charges.get'))
        ->assertForbidden();
});

test('admin can view charges page', function () {
    Charge::factory()->create();

    $this->actingAs(adminUser())
        ->get(route('charges.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/charges')
            ->has('charges', 1)
            ->has('renters'));
});

test('admin can create charge', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('charges.create'), chargePayload([
            'user_id' => $renter->id,
            'total_amount' => 600,
            'paid_amount' => 600,
            'status' => ChargeStatus::Paid->value,
        ]))
        ->assertRedirect(route('charges.get'));

    $this->assertDatabaseHas('charges', [
        'user_id' => $renter->id,
        'total_amount' => 600,
        'paid_amount' => 600,
        'status' => ChargeStatus::Paid->value,
    ]);
});

test('admin can create charge without last payment date', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('charges.create'), chargePayload([
            'user_id' => $renter->id,
            'last_payment_date' => null,
            'status' => ChargeStatus::Debt->value,
        ]))
        ->assertRedirect(route('charges.get'));

    $this->assertDatabaseHas('charges', [
        'user_id' => $renter->id,
        'last_payment_date' => null,
        'status' => ChargeStatus::Debt->value,
    ]);
});

test('charge user must be renter', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs(adminUser())
        ->post(route('charges.create'), chargePayload([
            'user_id' => $admin->id,
        ]))
        ->assertSessionHasErrors('user_id');
});

test('admin can update charge', function () {
    $charge = Charge::factory()->create([
        'total_amount' => 300,
        'paid_amount' => 100,
        'status' => ChargeStatus::Debt,
    ]);

    $this->actingAs(adminUser())
        ->put(route('charges.update', $charge->id), [
            'user_id' => $charge->user_id,
            'total_amount' => 300,
            'paid_amount' => 300,
            'last_payment_date' => '2026-06-15',
            'status' => ChargeStatus::Paid->value,
        ])
        ->assertRedirect(route('charges.get'));

    expect($charge->fresh()->paid_amount)->toBe('300.00');
    expect($charge->fresh()->status)->toBe(ChargeStatus::Paid);
    expect($charge->fresh()->last_payment_date?->format('Y-m-d'))->toBe('2026-06-15');
});

test('charge requires valid status', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('charges.create'), chargePayload([
            'user_id' => $renter->id,
            'status' => 'invalid',
        ]))
        ->assertSessionHasErrors('status');
});

test('charge display status is unpaid before due date', function () {
    $this->travelTo('2026-06-15');

    $charge = Charge::factory()->create([
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-20',
    ]);

    expect($charge->displayStatus())->toBe('unpaid');
});

test('charge display status is debt on due date', function () {
    $charge = Charge::factory()->create([
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-15',
    ]);

    expect($charge->displayStatus())->toBe('debt');
});

test('charge display status is debt after due date', function () {
    $charge = Charge::factory()->create([
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-15',
    ]);

    expect($charge->displayStatus())->toBe('debt');
});

test('admin charges page includes display status', function () {
    Charge::factory()->create([
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-20',
    ]);

    $this->actingAs(adminUser())
        ->get(route('charges.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('charges.0.display_status', 'unpaid'));
});

test('renter charges page includes display status as debt when overdue', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'status' => ChargeStatus::Debt,
        'last_payment_date' => '2026-06-15',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('charges.0.display_status', 'debt'));
});
