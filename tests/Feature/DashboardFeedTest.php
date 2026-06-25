<?php

use App\Models\Charge;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('admin dashboard includes notifications and news feed', function () {
    $admin = adminUser();

    $admin->news()->create([
        'title' => 'Плановое отключение воды',
        'text' => 'Завтра с 10:00 до 14:00.',
        'date' => '2026-06-20',
    ]);

    $admin->news()->create([
        'title' => 'Старое объявление',
        'text' => 'Текст',
        'date' => '2026-05-01',
    ]);

    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create(['user_id' => $renter->id]);
    $payment = Payment::factory()->create(['charge_id' => $charge->id]);

    Notification::send($admin, new PaymentSubmittedNotification($payment));

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('dashboardNews', 2)
            ->where('dashboardNews.0.title', 'Плановое отключение воды')
            ->where('dashboardNotifications.unread_count', 1)
            ->has('dashboardNotifications.items', 1)
            ->where('dashboardNotifications.items.0.title', 'Новый платёж на подтверждение'));
});

test('renter dashboard includes news in personal cabinet feed', function () {
    adminUser()->news()->create([
        'title' => 'Test',
        'text' => 'Text',
        'date' => '2026-06-01',
    ]);

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('dashboardNotifications', null)
            ->where('dashboardNews', null)
            ->has('renterDashboard.news', 1));
});
