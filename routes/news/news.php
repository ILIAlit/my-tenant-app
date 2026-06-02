

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\News\AdminNewsController;
use App\Http\Controllers\News\NewsController;
use App\Enums\UserRole;



Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    Route::post('news', [AdminNewsController::class, 'createNews'])->name('news.create');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('news', [NewsController::class, 'getNews'])->name('news.get');
});
