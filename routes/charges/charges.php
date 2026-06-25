<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Charges\AdminChargesController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('charges', [AdminChargesController::class, 'index'])->name('charges.get');
    Route::post('charges', [AdminChargesController::class, 'store'])->name('charges.create');
    Route::put('charges/{id}', [AdminChargesController::class, 'update'])->name('charges.update');
    Route::delete('charges/{id}', [AdminChargesController::class, 'destroy'])->name('charges.destroy');
});
