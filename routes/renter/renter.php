<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Renter\AdminRenterController;
use App\Http\Controllers\Admin\Renter\AdminRenterSettingsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('renter', [AdminRenterController::class, 'getRenters'])->name('renters.get');
    Route::post('renter', [AdminRenterController::class, 'createRenter'])->name('renters.create');
    Route::put('renter/{id}', [AdminRenterController::class, 'updateRenter'])->name('renters.update');
    Route::delete('renter/{id}', [AdminRenterController::class, 'deleteRenter'])->name('renters.delete');

    Route::get('renter/{id}/settings', [AdminRenterSettingsController::class, 'showSettings'])->name('renters.settings');
    Route::put('renter/{id}/room', [AdminRenterSettingsController::class, 'assignRoom'])->name('renters.assign-room');
    Route::put('renter/{id}/contract', [AdminRenterSettingsController::class, 'upsertContract'])->name('renters.contract');
    Route::put('renter/{id}/initial-meter-readings', [AdminRenterSettingsController::class, 'upsertInitialMeterReadings'])->name('renters.initial-meter-readings');
    Route::post('renter/{id}/services', [AdminRenterSettingsController::class, 'storeService'])->name('renters.services.store');
    Route::delete('renter/{id}/services/{serviceId}', [AdminRenterSettingsController::class, 'destroyService'])->name('renters.services.destroy');
});
