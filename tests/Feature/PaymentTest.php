<?php

use App\Enums\ChargeStatus;
use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function renterWithCharge(float $total = 500, float $paid = 0): array
{
    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => $total,
        'paid_amount' => $paid,
        'status' => ChargeStatus::Unpaid,
        'last_payment_date' => '2026-06-01',
    ]);

    return [$renter, $charge];
}

test('guest cannot submit payment', function () {
    [, $charge] = renterWithCharge();

    $this->post(route('renter.payments.store', $charge->id), [])
        ->assertRedirect(route('login'));
});

test('renter can submit payment with receipt', function () {
    Storage::fake('public');

    [$renter, $charge] = renterWithCharge(500, 0);

    $this->actingAs($renter)
        ->post(route('renter.payments.store', $charge->id), [
            'amount' => 200,
            'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
        ])
        ->assertRedirect(route('renter.charges'));

    $payment = Payment::query()->first();

    expect($payment)->not->toBeNull();
    expect((float) $payment->amount)->toBe(200.0);
    expect($payment->status)->toBe(PaymentStatus::Pending);
    Storage::disk('public')->assertExists($payment->receipt_path);

    expect($charge->fresh()->status)->toBe(ChargeStatus::Pending);
    expect((float) $charge->fresh()->paid_amount)->toBe(0.0);
});

test('renter cannot pay more than remaining amount', function () {
    Storage::fake('public');

    [$renter, $charge] = renterWithCharge(500, 0);

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 300,
        'status' => PaymentStatus::Pending,
    ]);

    $this->actingAs($renter)
        ->post(route('renter.payments.store', $charge->id), [
            'amount' => 250,
            'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
        ])
        ->assertSessionHasErrors('amount');
});

test('renter cannot pay already paid charge', function () {
    Storage::fake('public');

    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 500,
        'status' => ChargeStatus::Paid,
    ]);

    $this->actingAs($renter)
        ->post(route('renter.payments.store', $charge->id), [
            'amount' => 100,
            'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
        ])
        ->assertSessionHasErrors('charge_id');
});

test('renter cannot pay another renters charge', function () {
    Storage::fake('public');

    [, $charge] = renterWithCharge();
    $otherRenter = User::factory()->renter()->create();

    $this->actingAs($otherRenter)
        ->post(route('renter.payments.store', $charge->id), [
            'amount' => 100,
            'receipt' => UploadedFile::fake()->create('receipt.jpg', 100, 'image/jpeg'),
        ])
        ->assertSessionHasErrors('charge_id');
});

test('admin can view payments page', function () {
    Payment::factory()->create();

    $this->actingAs(adminUser())
        ->get(route('payments.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/payments')
            ->has('payments', 1));
});

test('admin can approve payment and update charge', function () {
    [$renter, $charge] = renterWithCharge(500, 0);
    $charge->update(['last_payment_date' => '2026-07-08']);

    $payment = Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 500,
        'status' => PaymentStatus::Pending,
    ]);

    $charge->update(['status' => ChargeStatus::Pending]);

    $this->actingAs(adminUser())
        ->put(route('payments.approve', $payment->id))
        ->assertRedirect(route('payments.get'));

    expect($payment->fresh()->status)->toBe(PaymentStatus::Approved);
    expect($charge->fresh()->status)->toBe(ChargeStatus::Paid);
    expect((float) $charge->fresh()->paid_amount)->toBe(500.0);
    expect($charge->fresh()->last_payment_date?->format('Y-m-d'))->toBe('2026-07-08');
});

test('admin can reject payment', function () {
    [, $charge] = renterWithCharge(500, 0);

    $payment = Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 200,
        'status' => PaymentStatus::Pending,
    ]);

    $charge->update(['status' => ChargeStatus::Pending]);

    $this->actingAs(adminUser())
        ->put(route('payments.reject', $payment->id))
        ->assertRedirect(route('payments.get'));

    expect($payment->fresh()->status)->toBe(PaymentStatus::Rejected);
    expect($charge->fresh()->status)->toBe(ChargeStatus::Debt);
    expect((float) $charge->fresh()->paid_amount)->toBe(0.0);
});

test('admin cannot approve already processed payment', function () {
    $payment = Payment::factory()->approved()->create();

    $this->actingAs(adminUser())
        ->put(route('payments.approve', $payment->id))
        ->assertSessionHasErrors('id');
});

test('renter charges include remaining amount', function () {
    [$renter, $charge] = renterWithCharge(500, 0);

    Payment::factory()->approved()->create([
        'charge_id' => $charge->id,
        'amount' => 100,
    ]);

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 150,
        'status' => PaymentStatus::Pending,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.charges'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('charges.0.remaining_amount', 250));
});

test('guest cannot access renter payments page', function () {
    $this->get(route('renter.payments'))->assertRedirect(route('login'));
});

test('admin cannot access renter payments page', function () {
    $this->actingAs(adminUser())
        ->get(route('renter.payments'))
        ->assertForbidden();
});

test('renter can view own payments', function () {
    [$renter, $charge] = renterWithCharge();

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'amount' => 150,
        'status' => PaymentStatus::Pending,
    ]);

    Payment::factory()->create([
        'charge_id' => Charge::factory()->create()->id,
        'amount' => 999,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.payments'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('renter/payments')
            ->has('payments', 1)
            ->where('payments.0.amount', 150));
});
