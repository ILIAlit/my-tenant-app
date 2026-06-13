<?php

use App\Enums\UserRole;
use App\Models\Rooms;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('renter can view rented rooms page', function () {
    $renter = User::factory()->create([
        'login' => 'renter-rooms',
        'role' => UserRole::RENTER->value,
    ]);

    Rooms::create([
        'user_id' => $renter->id,
        'number' => 101,
        'floor' => 1,
        'square' => 20,
        'status' => 'used',
    ]);

    $this->actingAs($renter)
        ->get(route('rooms.get-renter-rooms'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rent/rooms')
            ->has('rooms', 1));
});

test('renter cannot access admin rooms page', function () {
    $renter = User::factory()->create([
        'login' => 'renter-admin-rooms',
        'role' => UserRole::RENTER->value,
    ]);

    $this->actingAs($renter)
        ->get(route('rooms.get'))
        ->assertForbidden();
});
