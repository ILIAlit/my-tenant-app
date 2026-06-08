<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Enums\UserRole;
use App\Http\Controllers\Invoices\InvoicesController;


Route::middleware([RoleMiddleware::class . ':' . UserRole::ADMIN->value])->group(function () {
    
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('invoices', [InvoicesController::class, 'getRenterInvoices'])->name('invoices.get');
    Route::post('invoices/payment', [InvoicesController::class, 'processPayment'])->name('invoices.payment-process');
});
