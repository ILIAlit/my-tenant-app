<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/renter/renter.php';
require __DIR__.'/news/news.php';
require __DIR__.'/rooms/rooms.php';
require __DIR__.'/amenities/amenities.php';
require __DIR__.'/invoices/invoices.php';
require __DIR__.'/contracts/contracts.php';
require __DIR__.'/utility-readings/utility-readings.php';
require __DIR__.'/notifications/notifications.php';
