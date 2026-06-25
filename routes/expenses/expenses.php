<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Expenses\AdminExpensesController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('expenses', [AdminExpensesController::class, 'index'])->name('expenses.get');
    Route::post('expenses', [AdminExpensesController::class, 'store'])->name('expenses.create');
    Route::put('expenses/{id}', [AdminExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{id}', [AdminExpensesController::class, 'destroy'])->name('expenses.delete');
});
