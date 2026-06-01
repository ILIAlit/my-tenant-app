

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Enums\UserRole;



Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::inertia('renter', 'admin/renter')->name('renters.get');
});
