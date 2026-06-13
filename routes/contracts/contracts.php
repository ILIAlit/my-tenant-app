<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Contracts\AdminContractsController;
use App\Http\Controllers\Contracts\ContractsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('admin/contracts', [AdminContractsController::class, 'getAllContracts'])->name('contracts.all-get');
    Route::get('contracts/room/{id}', [AdminContractsController::class, 'index'])->name('contracts.admin-get');
    Route::post('contracts', [AdminContractsController::class, 'create'])->name('contracts.create');
    Route::put('contracts/{id}', [AdminContractsController::class, 'update'])->name('contracts.update');
    Route::delete('contracts/{id}/{rooms_id}', [AdminContractsController::class, 'delete'])->name('contracts.delete');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('contracts', [ContractsController::class, 'getRenterContracts'])->name('contracts.get');
});
