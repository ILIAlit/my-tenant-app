<?php

use App\Http\Controllers\Notifications\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::put('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::put('notifications/{id}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('notifications/{id}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');
});
