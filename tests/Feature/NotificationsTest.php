<?php

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UtilityReadingStatus;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\Payments;
use App\Models\Rooms;
use App\Models\User;
use App\Models\UtilityReading;
use App\Models\UtilityTariff;
use App\Notifications\UtilityReadingStatusNotification;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-06-13');

    $this->renter = User::factory()->create([
        'login' => 'renter1',
        'role' => UserRole::RENTER->value,
    ]);

    $this->admin = User::factory()->create([
        'login' => 'admin1',
        'role' => UserRole::ADMIN->value,
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
        'conclusion_date' => '2026-01-15',
        'expiration_date' => '2027-01-15',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    $this->invoice = Invoices::create([
        'user_id' => $this->renter->id,
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'name' => 'Начисление за комнату № 101',
        'total_price' => 15000,
        'create_date' => '15.04.2026',
        'due_date' => '15.05.2026',
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('approving a payment notifies the renter', function () {
    $payment = Payments::create([
        'invoices_id' => $this->invoice->id,
        'amount' => 5000,
        'status' => PaymentStatus::Review->value,
    ]);

    $this->actingAs($this->admin)
        ->put(route('payments.approve', ['id' => $payment->id]))
        ->assertRedirect(route('payments.admin-get'));

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('payment')
        ->and($notification->data['status'])->toBe('approved')
        ->and($notification->data['title'])->toBe('Платёж одобрен')
        ->and($notification->read_at)->toBeNull();
});

test('rejecting a payment notifies the renter with reason', function () {
    $payment = Payments::create([
        'invoices_id' => $this->invoice->id,
        'amount' => 5000,
        'status' => PaymentStatus::Review->value,
    ]);

    $this->actingAs($this->admin)
        ->put(route('payments.reject', ['id' => $payment->id]), [
            'rejection_reason' => 'Чек нечитаем',
        ])
        ->assertRedirect(route('payments.admin-get'));

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('payment')
        ->and($notification->data['status'])->toBe('rejected')
        ->and($notification->data['rejection_reason'])->toBe('Чек нечитаем');
});

test('approving utility readings notifies the renter', function () {
    UtilityTariff::current()->update([
        'cold_water_rate' => 50,
        'hot_water_rate' => 0,
        'electricity_rate' => 0,
    ]);

    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.approve', ['id' => $reading->id]))
        ->assertRedirect(route('utility-readings.all-get'));

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('utility_reading')
        ->and($notification->data['status'])->toBe('approved')
        ->and($notification->data['utility_amount'])->toBe(500);
});

test('rejecting utility readings notifies the renter', function () {
    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.reject', ['id' => $reading->id]), [
            'rejection_reason' => 'Фото нечитаемо',
        ])
        ->assertRedirect(route('utility-readings.all-get'));

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('utility_reading')
        ->and($notification->data['status'])->toBe('rejected')
        ->and($notification->data['rejection_reason'])->toBe('Фото нечитаемо');
});

test('renter can view notifications page', function () {
    $this->renter->notify(new UtilityReadingStatusNotification(
        UtilityReading::create([
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-04-15',
            'period_end' => '2026-05-15',
            'cold_water' => 10,
            'submitted_by' => $this->renter->id,
            'status' => UtilityReadingStatus::Approved,
        ])
    ));

    $this->actingAs($this->renter)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notifications/notifications')
            ->has('notifications', 1));
});

test('renter can mark a notification as read', function () {
    $payment = Payments::create([
        'invoices_id' => $this->invoice->id,
        'amount' => 5000,
        'status' => PaymentStatus::Review->value,
    ]);

    $this->actingAs($this->admin)
        ->put(route('payments.approve', ['id' => $payment->id]));

    $notification = $this->renter->fresh()->notifications()->first();

    $this->actingAs($this->renter)
        ->put(route('notifications.read', ['id' => $notification->id]))
        ->assertRedirect();

    expect($this->renter->fresh()->unreadNotifications()->count())->toBe(0);
});

test('renter can mark all notifications as read', function () {
    foreach (range(1, 3) as $i) {
        $payment = Payments::create([
            'invoices_id' => $this->invoice->id,
            'amount' => 1000,
            'status' => PaymentStatus::Review->value,
        ]);

        $this->actingAs($this->admin)
            ->put(route('payments.approve', ['id' => $payment->id]));
    }

    expect($this->renter->fresh()->unreadNotifications()->count())->toBe(3);

    $this->actingAs($this->renter)
        ->put(route('notifications.read-all'))
        ->assertRedirect();

    expect($this->renter->fresh()->unreadNotifications()->count())->toBe(0);
});

test('renter cannot mark another users notification as read', function () {
    $payment = Payments::create([
        'invoices_id' => $this->invoice->id,
        'amount' => 5000,
        'status' => PaymentStatus::Review->value,
    ]);

    $this->actingAs($this->admin)
        ->put(route('payments.approve', ['id' => $payment->id]));

    $notification = $this->renter->fresh()->notifications()->first();

    $otherRenter = User::factory()->create([
        'login' => 'renter2',
        'role' => UserRole::RENTER->value,
    ]);

    $this->actingAs($otherRenter)
        ->put(route('notifications.read', ['id' => $notification->id]));

    expect($this->renter->fresh()->unreadNotifications()->count())->toBe(1);
});
