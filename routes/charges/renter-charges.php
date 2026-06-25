<?php

use App\Enums\UserRole;
use App\Http\Controllers\Renter\RenterChargesController;
use App\Http\Controllers\Renter\RenterPaymentsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::RENTER->value])->group(function () {
    Route::get('charges/my', [RenterChargesController::class, 'index'])->name('renter.charges');
    Route::get('payments/my', [RenterPaymentsController::class, 'index'])->name('renter.payments');
    Route::post('charges/my/{chargeId}/payments', [RenterPaymentsController::class, 'store'])->name('renter.payments.store');
});
