<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Invoices\AdminInvoicesController;
use App\Http\Controllers\Admin\Payments\AdminPaymentsController;
use App\Http\Controllers\Invoices\InvoicesController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([RoleMiddleware::class.':'.UserRole::ADMIN->value])->group(function () {
    Route::get('admin/invoices', [AdminInvoicesController::class, 'getInvoices'])->name('invoices.admin-get');
    Route::post('admin/invoices', [AdminInvoicesController::class, 'createInvoice'])->name('invoices.create');
    Route::put('admin/invoices/{id}', [AdminInvoicesController::class, 'updateInvoice'])->name('invoices.update');
    Route::delete('admin/invoices/{id}', [AdminInvoicesController::class, 'deleteInvoice'])->name('invoices.delete');

    Route::get('admin/payments', [AdminPaymentsController::class, 'getPayments'])->name('payments.admin-get');
    Route::put('admin/payments/{id}/approve', [AdminPaymentsController::class, 'approvePayment'])->name('payments.approve');
    Route::put('admin/payments/{id}/reject', [AdminPaymentsController::class, 'rejectPayment'])->name('payments.reject');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('invoices', [InvoicesController::class, 'getRenterInvoices'])->name('invoices.get');
    Route::get('payments', [InvoicesController::class, 'getRenterPayments'])->name('payments.get');
    Route::post('invoices/payment', [InvoicesController::class, 'processPayment'])->name('invoices.payment-process');
});
