<?php

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access contracts page', function () {
    $this->get(route('contracts.get'))->assertRedirect(route('login'));
});

test('renter cannot access contracts page', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('contracts.get'))
        ->assertForbidden();
});

test('admin can view contracts page', function () {
    $renter = User::factory()->renter()->create([
        'last_name' => 'Иванов',
        'name' => 'Иван',
    ]);

    Contract::factory()->create([
        'user_id' => $renter->id,
        'number' => 'ДГ-2026-001',
        'monthly_rent' => 500,
    ]);

    $this->actingAs(adminUser())
        ->get(route('contracts.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/contracts')
            ->has('contracts', 1)
            ->where('contracts.0.number', 'ДГ-2026-001')
            ->where('contracts.0.renter.full_name', 'Иванов Иван'));
});

test('contracts are ordered by start date descending', function () {
    $renter = User::factory()->renter()->create();

    Contract::factory()->create([
        'user_id' => $renter->id,
        'number' => 'ДГ-OLD',
        'start_date' => '2024-01-01',
    ]);

    Contract::factory()->create([
        'user_id' => User::factory()->renter()->create()->id,
        'number' => 'ДГ-NEW',
        'start_date' => '2026-01-01',
    ]);

    $this->actingAs(adminUser())
        ->get(route('contracts.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('contracts.0.number', 'ДГ-NEW')
            ->where('contracts.1.number', 'ДГ-OLD'));
});
