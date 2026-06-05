<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Enums\UserRole;
use App\Http\Controllers\Admin\Amenities\AdminAmenitiesController;


Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::get('amenities/{id}', [AdminAmenitiesController::class, 'index'])->name('amenities.get');
    Route::post('amenities', [AdminAmenitiesController::class, 'create'])->name('amenities.create');
    Route::put('amenities/{id}', [AdminAmenitiesController::class, 'update'])->name('amenities.update');
    Route::delete('amenities/{id}/{rooms_id}', [AdminAmenitiesController::class, 'delete'])->name('amenities.delete');
});
