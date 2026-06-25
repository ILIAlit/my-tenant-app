<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Contracts\AdminContractsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('contracts', [AdminContractsController::class, 'index'])->name('contracts.get');
});
