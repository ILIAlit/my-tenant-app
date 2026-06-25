<?php

use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access renter contract page', function () {
    $this->get(route('renter.contract'))->assertRedirect(route('login'));
});

test('admin cannot access renter contract page', function () {
    $this->actingAs(adminUser())
        ->get(route('renter.contract'))
        ->assertForbidden();
});

test('renter can view contract page with contract and room', function () {
    $renter = User::factory()->renter()->create();

    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'number' => '205',
        'floor' => 2,
        'area' => 18.5,
    ]);

    Contract::factory()->create([
        'user_id' => $renter->id,
        'number' => 'ДГ-2026-010',
        'monthly_rent' => 420,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.contract'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('renter/contract')
            ->where('contract.number', 'ДГ-2026-010')
            ->where('contract.monthly_rent', 420)
            ->where('room.number', '205')
            ->where('room.floor', 2)
            ->where('room.area', 18.5));
});

test('renter sees empty state without contract and room', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('renter.contract'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('renter/contract')
            ->where('contract', null)
            ->where('room', null));
});

test('renter only sees own contract and room', function () {
    $renter = User::factory()->renter()->create();
    $otherRenter = User::factory()->renter()->create();

    Room::factory()->create([
        'user_id' => $renter->id,
        'number' => '101',
        'floor' => 1,
        'area' => 20,
    ]);

    Contract::factory()->create([
        'user_id' => $otherRenter->id,
        'number' => 'ДГ-OTHER',
    ]);

    Contract::factory()->create([
        'user_id' => $renter->id,
        'number' => 'ДГ-MINE',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.contract'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('contract.number', 'ДГ-MINE')
            ->where('room.number', '101'));
});
