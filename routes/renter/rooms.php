<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\Rooms\AdminRoomsController;
use App\Enums\UserRole;

Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::post('rooms', [AdminRoomsController::class, 'createRooms'])->name('rooms.create');
    Route::delete('rooms/{room}', [AdminRoomsController::class, 'deleteRooms'])->name('rooms.delete');
    Route::put('rooms/{room}', [AdminRoomsController::class, 'updateRooms'])->name('rooms.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('rooms', [AdminRoomsController::class, 'getRooms'])->name('rooms.get');
});
