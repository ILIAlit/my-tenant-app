<?php

use App\Enums\UserRole;
use App\Http\Controllers\Renter\RenterMeterReadingsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::RENTER->value])->group(function () {
    Route::get('meter-readings/my', [RenterMeterReadingsController::class, 'index'])->name('renter.meter-readings');
    Route::post('meter-readings/my', [RenterMeterReadingsController::class, 'store'])->name('renter.meter-readings.store');
});
