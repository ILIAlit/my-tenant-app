<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\MeterReadings\AdminMeterReadingsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('meter-readings', [AdminMeterReadingsController::class, 'index'])->name('meter-readings.get');
    Route::put('meter-readings/tariffs', [AdminMeterReadingsController::class, 'updateTariffs'])->name('meter-readings.tariffs.update');
    Route::post('meter-readings', [AdminMeterReadingsController::class, 'store'])->name('meter-readings.create');
    Route::put('meter-readings/{id}', [AdminMeterReadingsController::class, 'update'])->name('meter-readings.update');
    Route::put('meter-readings/{id}/approve', [AdminMeterReadingsController::class, 'approve'])->name('meter-readings.approve');
    Route::put('meter-readings/{id}/reject', [AdminMeterReadingsController::class, 'reject'])->name('meter-readings.reject');
    Route::delete('meter-readings/{id}', [AdminMeterReadingsController::class, 'destroy'])->name('meter-readings.destroy');
});
