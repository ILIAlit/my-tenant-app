<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Payments\AdminPaymentsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('payments', [AdminPaymentsController::class, 'index'])->name('payments.get');
    Route::put('payments/{id}/approve', [AdminPaymentsController::class, 'approve'])->name('payments.approve');
    Route::put('payments/{id}/reject', [AdminPaymentsController::class, 'reject'])->name('payments.reject');
});
