<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Rooms\AdminRoomsController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('rooms', [AdminRoomsController::class, 'index'])->name('rooms.get');
    Route::post('rooms', [AdminRoomsController::class, 'store'])->name('rooms.create');
    Route::put('rooms/{id}', [AdminRoomsController::class, 'update'])->name('rooms.update');
    Route::delete('rooms/{id}', [AdminRoomsController::class, 'destroy'])->name('rooms.delete');
});
