<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\Rooms\AdminRoomsController;
use App\Http\Controllers\Admin\Rooms\AdminEditRoomsController;
use App\Http\Controllers\Rooms\RoomsController;
use App\Enums\UserRole;

Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::get('rooms', [AdminRoomsController::class, 'getRooms'])->name('rooms.get');
    Route::post('rooms', [AdminRoomsController::class, 'createRooms'])->name('rooms.create');
    Route::delete('rooms/{id}', [AdminRoomsController::class, 'deleteRooms'])->name('rooms.delete');

    Route::put('rooms/{id}', [AdminEditRoomsController::class, 'updateRooms'])->name('rooms.update');
    Route::get('rooms/room-add-renter/{id}', [AdminEditRoomsController::class, 'getAddRenterToRoom'])->name('rooms.get-add-renter-to-room');
    Route::get('rooms/room-update/{id}', [AdminEditRoomsController::class, 'getUpdateRooms'])->name('rooms.get-update');
    Route::put('rooms/add-renter/{room_id}/{renter_id}', [AdminEditRoomsController::class, 'addRenterToRoom'])->name('rooms.add-renter');
    Route::put('rooms/room-delete-renter/{id}', [AdminEditRoomsController::class, 'deleteRenterFromRoom'])->name('rooms.delete-renter');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('rooms/renter-rooms', [RoomsController::class, 'getRenterRooms'])->name('rooms.get-renter-rooms');
});
