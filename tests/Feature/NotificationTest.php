<?php

use App\Enums\MeterType;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Notifications\ChargeCreatedNotification;
use App\Notifications\MeterReadingSubmittedNotification;
use App\Notifications\NewsPublishedNotification;
use App\Notifications\PaymentApprovedNotification;
use App\Notifications\PaymentRejectedNotification;
use App\Notifications\PaymentSubmittedNotification;
use App\Notifications\RoomAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('renter receives notification when assigned to room', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create(['status' => RoomStatus::Free]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertRedirect(route('renters.settings', $renter->id));

    Notification::assertSentTo($renter, RoomAssignedNotification::class);
});

test('renter does not receive room notification when room is removed', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => null,
        ])
        ->assertRedirect(route('renters.settings', $renter->id));

    Notification::assertNothingSentTo($renter);
});

test('renter receives notification when charge is created by admin', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('charges.create'), [
            'user_id' => $renter->id,
            'total_amount' => 400,
            'paid_amount' => 0,
            'last_payment_date' => null,
            'status' => 'debt',
        ])
        ->assertRedirect(route('charges.get'));

    Notification::assertSentTo($renter, ChargeCreatedNotification::class);
});

test('renter receives notification when payment is approved', function () {
    Notification::fake();

    [$renter, $charge] = (function () {
        $renter = User::factory()->renter()->create();
        $charge = Charge::factory()->create(['user_id' => $renter->id]);

        return [$renter, $charge];
    })();

    $payment = Payment::factory()->create(['charge_id' => $charge->id]);

    $this->actingAs(adminUser())
        ->put(route('payments.approve', $payment->id))
        ->assertRedirect(route('payments.get'));

    Notification::assertSentTo($renter, PaymentApprovedNotification::class);
});

test('renter receives notification when payment is rejected', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create(['user_id' => $renter->id]);
    $payment = Payment::factory()->create(['charge_id' => $charge->id]);

    $this->actingAs(adminUser())
        ->put(route('payments.reject', $payment->id))
        ->assertRedirect(route('payments.get'));

    Notification::assertSentTo($renter, PaymentRejectedNotification::class);
});

test('admin receives notification when renter submits payment', function () {
    Notification::fake();
    Storage::fake('public');

    $admin = adminUser();
    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create(['user_id' => $renter->id]);

    $this->actingAs($renter)
        ->post(route('renter.payments.store', $charge->id), [
            'amount' => 150,
            'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
        ])
        ->assertRedirect(route('renter.charges'));

    Notification::assertSentTo($admin, PaymentSubmittedNotification::class);
    Notification::assertNotSentTo($renter, PaymentSubmittedNotification::class);
});

test('admin receives notification when renter submits meter reading', function () {
    Notification::fake();

    $admin = adminUser();
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->post(route('renter.meter-readings.store'), [
            'type' => MeterType::ColdWater->value,
            'reading_date' => '2026-06-10',
            'value' => 120.5,
        ])
        ->assertRedirect(route('renter.meter-readings'));

    Notification::assertSentTo($admin, MeterReadingSubmittedNotification::class);
    Notification::assertNotSentTo($renter, MeterReadingSubmittedNotification::class);
});

test('users receive notification when news is published', function () {
    Notification::fake();

    $admin = adminUser();
    $renter = User::factory()->renter()->create();

    $this->actingAs($admin)
        ->post(route('news.create'), [
            'title' => 'Важное объявление',
            'text' => 'Текст объявления',
            'date' => '2026-06-16',
        ])
        ->assertRedirect(route('news.get'));

    Notification::assertSentTo($renter, NewsPublishedNotification::class);
    Notification::assertNotSentTo($admin, NewsPublishedNotification::class);
});

test('user can mark notification as read', function () {
    $renter = User::factory()->renter()->create();
    $renter->notify(new ChargeCreatedNotification(Charge::factory()->create([
        'user_id' => $renter->id,
    ])));

    $notification = $renter->unreadNotifications->first();

    expect($renter->unreadNotifications)->toHaveCount(1);

    $this->actingAs($renter)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($renter->fresh()->unreadNotifications)->toHaveCount(0);
});

test('user can mark all notifications as read', function () {
    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create(['user_id' => $renter->id]);

    $renter->notify(new ChargeCreatedNotification($charge));
    $renter->notify(new ChargeCreatedNotification($charge));

    expect($renter->unreadNotifications)->toHaveCount(2);

    $this->actingAs($renter)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($renter->fresh()->unreadNotifications)->toHaveCount(0);
});

test('shared notifications are available in inertia', function () {
    $renter = User::factory()->renter()->create();
    $renter->notify(new ChargeCreatedNotification(Charge::factory()->create([
        'user_id' => $renter->id,
    ])));

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('notifications')
            ->where('notifications.unread_count', 1)
            ->has('notifications.items', 1));
});
