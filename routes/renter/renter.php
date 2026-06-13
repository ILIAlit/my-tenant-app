

<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Renter\AdminRenterController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('renter', [AdminRenterController::class, 'getRenters'])->name('renters.get');
    Route::put('renter/{id}', [AdminRenterController::class, 'updateRenters'])->name('renters.update');
    Route::delete('renter/{id}', [AdminRenterController::class, 'deleteRenters'])->name('renters.delete');
});
