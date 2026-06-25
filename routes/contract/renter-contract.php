<?php

use App\Enums\UserRole;
use App\Http\Controllers\Renter\RenterContractController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::RENTER->value])->group(function () {
    Route::get('contract', [RenterContractController::class, 'show'])->name('renter.contract');
});
