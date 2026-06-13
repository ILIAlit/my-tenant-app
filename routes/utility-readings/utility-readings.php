<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\UtilityReadings\AdminUtilityReadingsController;
use App\Http\Controllers\UtilityReadings\UtilityReadingsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('admin/utility-readings', [AdminUtilityReadingsController::class, 'getAllReadings'])->name('utility-readings.all-get');
    Route::post('admin/utility-tariffs', [AdminUtilityReadingsController::class, 'updateTariffs'])->name('utility-tariffs.update');
    Route::get('utility-readings/room/{id}', [AdminUtilityReadingsController::class, 'index'])->name('utility-readings.admin-get');
    Route::put('utility-readings/{id}', [AdminUtilityReadingsController::class, 'update'])->name('utility-readings.update');
    Route::put('utility-readings/{id}/approve', [AdminUtilityReadingsController::class, 'approve'])->name('utility-readings.approve');
    Route::put('utility-readings/{id}/reject', [AdminUtilityReadingsController::class, 'reject'])->name('utility-readings.reject');
    Route::delete('utility-readings/{id}/{rooms_id}', [AdminUtilityReadingsController::class, 'delete'])->name('utility-readings.delete');
});

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::RENTER->value])->group(function () {
    Route::get('utility-readings', [UtilityReadingsController::class, 'index'])->name('utility-readings.get');
    Route::post('utility-readings', [UtilityReadingsController::class, 'create'])->name('utility-readings.create');
});
