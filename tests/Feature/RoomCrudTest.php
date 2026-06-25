<?php

use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function roomPayload(array $overrides = []): array
{
    return array_merge([
        'type' => RoomType::Room->value,
        'number' => '12А',
        'floor' => 3,
        'area' => 45.5,
        'status' => RoomStatus::Free->value,
        'last_repair_date' => '2024-06-01',
        'notes' => 'Окна выходят во двор',
    ], $overrides);
}

test('guest cannot access rooms page', function () {
    $this->get(route('rooms.get'))->assertRedirect(route('login'));
});

test('renter cannot access rooms page', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('rooms.get'))
        ->assertForbidden();
});

test('admin can view rooms page', function () {
    Room::factory()->create(['number' => '101', 'floor' => 1]);

    $this->actingAs(adminUser())
        ->get(route('rooms.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/rooms')
            ->has('rooms', 1)
            ->where('rooms.0.type', RoomType::Room->value));
});

test('admin can create room', function () {
    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload())
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseHas('rooms', [
        'type' => RoomType::Room->value,
        'number' => '12А',
        'floor' => 3,
        'status' => RoomStatus::Free->value,
    ]);
});

test('admin can create garage without floor', function () {
    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload([
            'type' => RoomType::Garage->value,
            'number' => 'G-12',
            'floor' => null,
        ]))
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseHas('rooms', [
        'type' => RoomType::Garage->value,
        'number' => 'G-12',
        'floor' => null,
    ]);
});

test('room requires floor', function () {
    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload([
            'floor' => null,
        ]))
        ->assertSessionHasErrors('floor');
});

test('admin can create garage', function () {
    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload([
            'type' => RoomType::Garage->value,
            'number' => 'G-99',
            'floor' => 1,
        ]))
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseHas('rooms', [
        'type' => RoomType::Garage->value,
        'number' => 'G-99',
        'floor' => 1,
    ]);
});

test('admin can create room without optional fields', function () {
    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload([
            'last_repair_date' => null,
            'notes' => null,
        ]))
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseHas('rooms', [
        'number' => '12А',
        'last_repair_date' => null,
        'notes' => null,
    ]);
});

test('room number must be unique within the same type', function () {
    Room::factory()->create(['type' => RoomType::Room, 'number' => '12А']);

    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload())
        ->assertSessionHasErrors('number');
});

test('room number can repeat across different types', function () {
    Room::factory()->create(['type' => RoomType::Room, 'number' => '12']);

    $this->actingAs(adminUser())
        ->post(route('rooms.create'), roomPayload([
            'type' => RoomType::Garage->value,
            'number' => '12',
            'floor' => null,
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseHas('rooms', [
        'type' => RoomType::Garage->value,
        'number' => '12',
    ]);
});

test('admin can update room', function () {
    $room = Room::factory()->create([
        'number' => '5',
        'status' => RoomStatus::Free,
    ]);

    $this->actingAs(adminUser())
        ->put(route('rooms.update', $room->id), roomPayload([
            'number' => '5',
            'status' => RoomStatus::Repair->value,
            'notes' => 'Идёт ремонт',
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('rooms.get'));

    expect($room->fresh()->status)->toBe(RoomStatus::Repair);
    expect($room->fresh()->notes)->toBe('Идёт ремонт');
});

test('admin can delete room', function () {
    $room = Room::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('rooms.delete', $room->id))
        ->assertRedirect(route('rooms.get'));

    $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
});
