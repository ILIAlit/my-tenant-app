<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/renter/renter.php';
require __DIR__.'/rooms/rooms.php';
require __DIR__.'/contracts/contracts.php';
require __DIR__.'/contract/renter-contract.php';
require __DIR__.'/charges/charges.php';
require __DIR__.'/charges/renter-charges.php';
require __DIR__.'/meter-readings/meter-readings.php';
require __DIR__.'/meter-readings/renter-meter-readings.php';
require __DIR__.'/payments/payments.php';
require __DIR__.'/expenses/expenses.php';
require __DIR__.'/notifications.php';
require __DIR__.'/news/news.php';
