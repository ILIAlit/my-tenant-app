<?php

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\News;
use App\Models\Payments;
use App\Models\Rooms;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-06-13');

    $this->renter = User::factory()->create([
        'login' => 'renter1',
        'role' => UserRole::RENTER->value,
    ]);

    $this->room = Rooms::create([
        'user_id' => $this->renter->id,
        'number' => 101,
        'floor' => 1,
        'square' => 20,
        'status' => 'used',
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('renter dashboard shows debt, unpaid invoices, last payment and news', function () {
    // Просроченное и неоплаченное начисление — долг.
    $overdue = Invoices::create([
        'user_id' => $this->renter->id,
        'rooms_id' => $this->room->id,
        'name' => 'Начисление за май',
        'total_price' => 15000,
        'paid_price' => 5000,
        'create_date' => '15.04.2026',
        'due_date' => '15.05.2026',
    ]);

    // Полностью оплаченное — не попадает в неоплаченные.
    Invoices::create([
        'user_id' => $this->renter->id,
        'rooms_id' => $this->room->id,
        'name' => 'Начисление за март',
        'total_price' => 10000,
        'paid_price' => 10000,
        'create_date' => '15.03.2026',
        'due_date' => '15.04.2026',
    ]);

    Payments::create([
        'invoices_id' => $overdue->id,
        'amount' => 5000,
        'status' => PaymentStatus::Approved->value,
    ]);

    $news = new News([
        'title' => 'Плановое отключение воды',
        'text' => 'Завтра отключат воду с 10 до 14.',
        'date' => '2026-06-12',
    ]);
    $news->user_id = $this->renter->id;
    $news->save();

    $this->actingAs($this->renter)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.totalDebt', 10000)
            ->where('stats.totalUnpaid', 10000)
            ->where('stats.unpaidCount', 1)
            ->has('stats.unpaidInvoices', 1)
            ->where('stats.unpaidInvoices.0.name', 'Начисление за май')
            ->where('stats.unpaidInvoices.0.remaining', 10000)
            ->where('stats.lastPayment.amount', 5000)
            ->where('stats.lastPayment.status', 'approved')
            ->has('stats.news', 1)
            ->where('stats.news.0.title', 'Плановое отключение воды'));
});

test('renter dashboard handles no data gracefully', function () {
    $this->actingAs($this->renter)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.totalDebt', 0)
            ->where('stats.unpaidCount', 0)
            ->has('stats.unpaidInvoices', 0)
            ->where('stats.lastPayment', null)
            ->has('stats.news', 0));
});

test('admin dashboard does not receive renter stats', function () {
    $admin = User::factory()->create([
        'login' => 'admin1',
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats', null)
            ->has('adminStats'));
});

test('admin dashboard shows floor plan, payments, debtors and utilities', function () {
    $admin = User::factory()->create([
        'login' => 'admin1',
        'role' => UserRole::ADMIN->value,
    ]);

    // Вторая комната на другом этаже — свободная.
    Rooms::create([
        'number' => 201,
        'floor' => 2,
        'square' => 18,
        'status' => 'free',
    ]);

    $contract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-101',
        'conclusion_date' => '2026-01-15',
        'expiration_date' => '2027-01-15',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    // Просроченное неоплаченное начисление — делает арендатора должником.
    $overdue = Invoices::create([
        'user_id' => $this->renter->id,
        'rooms_id' => $this->room->id,
        'contracts_id' => $contract->id,
        'name' => 'Начисление за май',
        'total_price' => 15000,
        'paid_price' => 5000,
        'create_date' => '15.04.2026',
        'due_date' => '15.05.2026',
    ]);

    Payments::create([
        'invoices_id' => $overdue->id,
        'amount' => 5000,
        'status' => PaymentStatus::Approved->value,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('adminStats.roomStats.total', 2)
            ->where('adminStats.roomStats.free', 1)
            ->where('adminStats.roomStats.used', 1)
            ->has('adminStats.floors', 2)
            ->where('adminStats.floors.0.floor', 1)
            ->where('adminStats.floors.1.floor', 2)
            ->has('adminStats.recentPayments', 1)
            ->where('adminStats.recentPayments.0.amount', 5000)
            ->has('adminStats.debtors', 1)
            ->where('adminStats.debtors.0.debt', 10000)
            ->where('adminStats.debtors.0.invoices_count', 1));
});
