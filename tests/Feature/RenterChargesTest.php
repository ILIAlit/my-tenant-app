<?php

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access renter charges page', function () {
    $this->get(route('renter.charges'))->assertRedirect(route('login'));
});

test('admin cannot access renter charges page', function () {
    $this->actingAs(adminUser())
        ->get(route('renter.charges'))
        ->assertForbidden();
});

test('renter can view own charges', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 500,
        'created_at' => '2026-06-01 10:00:00',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('renter/charges')
            ->has('charges', 1)
            ->where('charges.0.total_amount', 500)
            ->where('charges.0.created_at', '2026-06-01'));
});

test('paid charge cannot be paid again', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 500,
        'status' => ChargeStatus::Paid,
        'created_at' => '2026-06-01 10:00:00',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('charges.0.display_status', 'paid')
            ->where('charges.0.can_pay', false));
});

test('renter only sees own charges', function () {
    $renter = User::factory()->renter()->create();
    $otherRenter = User::factory()->renter()->create();

    Charge::factory()->create(['user_id' => $renter->id, 'total_amount' => 100]);
    Charge::factory()->create(['user_id' => $otherRenter->id, 'total_amount' => 999]);

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('charges', 1)
            ->where('charges.0.total_amount', 100));
});

test('renter can filter charges by created date', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 100,
        'created_at' => '2026-05-01 10:00:00',
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 200,
        'created_at' => '2026-06-15 10:00:00',
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 300,
        'created_at' => '2026-07-01 10:00:00',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.charges', [
            'created_from' => '2026-06-01',
            'created_to' => '2026-06-30',
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('charges', 1)
            ->where('charges.0.total_amount', 200)
            ->where('filters.created_from', '2026-06-01')
            ->where('filters.created_to', '2026-06-30'));
});

test('created_to must be after or equal to created_from', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('renter.charges', [
            'created_from' => '2026-06-15',
            'created_to' => '2026-06-01',
        ]))
        ->assertSessionHasErrors('created_to');
});
