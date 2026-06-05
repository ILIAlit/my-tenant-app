

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\Renter\AdminRenterController;
use App\Enums\UserRole;



Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::get('renter', [AdminRenterController::class, 'getRenters'])->name('renters.get');
});
