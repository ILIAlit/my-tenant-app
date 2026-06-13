<?php

namespace App\Http\Controllers\Invoices;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\PaymentsProcessRequest;
use App\Models\Invoices;
use App\Models\Payments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvoicesController extends Controller
{
    public function getRenterInvoices(Request $request)
    {
        $invoices = $request->user()
            ->invoices()
            ->withExists(['payments as has_pending_payment' => function (Builder $query): void {
                $query->where('status', PaymentStatus::Review->value);
            }])
            ->latest()
            ->get()
            ->append('current_status');

        return Inertia::render('invoices/invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function getRenterPayments(Request $request)
    {
        $payments = $request->user()
            ->payments()
            ->with('invoice:id,name')
            ->latest()
            ->get()
            ->append('receipt_url');

        return Inertia::render('payments/payments', [
            'payments' => $payments,
        ]);
    }

    public function processPayment(PaymentsProcessRequest $request)
    {
        $validated = $request->validated();

        $invoice = Invoices::where('user_id', $request->user()->id)
            ->findOrFail($validated['invoices_id']);

        $paidAmount = (int) $validated['amount'];
        $remaining = $invoice->total_price - $invoice->paid_price;

        if ($remaining <= 0) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Сумма оплачена.')]);

            return to_route('invoices.get');
        }

        if ($paidAmount <= 0 || $paidAmount > $remaining) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Неверная сумма оплаты.')]);

            return to_route('invoices.get');
        }

        $receiptPath = $request->file('receipt')->store('payments/receipts', 'public');

        try {
            DB::transaction(function () use ($invoice, $paidAmount, $receiptPath) {
                $invoice = Invoices::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

                Payments::create([
                    'invoices_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'receipt_path' => $receiptPath,
                ]);

                $invoice->update([
                    'paid_price' => $invoice->paid_price + $paidAmount,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($receiptPath);
            Log::error('Payment processing failed', ['invoice_id' => $invoice->id, 'exception' => $e]);

            Inertia::flash('toast', ['type' => 'error', 'message' => __('Ошибка оплаты.')]);

            return to_route('invoices.get');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж отправлен.')]);

        return to_route('invoices.get');
    }
}
