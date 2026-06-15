<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoices;
use App\Models\Payments;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Payments\PaymentsProcessRequest;
use Illuminate\Support\Facades\Log;

class InvoicesController extends Controller
{
    public function getRenterInvoices(Request $request) {
        $user = $request->user();
        $invoices = $user->invoices;

        return Inertia::render('invoices/invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function processPayment(PaymentsProcessRequest $request)
    {
        $validated = $request->validated();
        
        $user = $request->user();
        $invoice = Invoices::where('user_id', $user->id)
        ->findOrFail($validated['invoices_id']);
        
        //Log::info($payment);
        return;

        $receiptPath = $request->file('receipt')->store('payments/receipts', 'public');

        Payments::create([
            'invoices_id' => $invoice->id,
            'amount' => $validated['amount'],
            'receipt_path' => $receiptPath,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж отправлен.')]);

        return to_route('invoices.get');
    }
}
