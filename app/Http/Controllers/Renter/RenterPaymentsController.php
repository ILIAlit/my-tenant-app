<?php

namespace App\Http\Controllers\Renter;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentStoreRequest;
use App\Models\Payment;
use App\Notifications\PaymentSubmittedNotification;
use App\Services\AdminNotifier;
use App\Services\ChargePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RenterPaymentsController extends Controller
{
    public function __construct(
        private ChargePaymentService $chargePaymentService,
        private AdminNotifier $adminNotifier,
    ) {}

    public function index(Request $request): Response
    {
        $payments = Payment::query()
            ->whereHas('charge', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('charge:id,total_amount,created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'status' => $payment->status->value,
                'receipt_url' => $payment->receiptUrl(),
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
                'charge' => [
                    'id' => $payment->charge->id,
                    'total_amount' => (float) $payment->charge->total_amount,
                    'created_at' => $payment->charge->created_at->format('Y-m-d'),
                ],
            ]);

        return Inertia::render('renter/payments', [
            'payments' => $payments,
        ]);
    }

    public function store(PaymentStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        $payment = Payment::create([
            'charge_id' => $validated['charge_id'],
            'amount' => $validated['amount'],
            'receipt_path' => $receiptPath,
            'status' => PaymentStatus::Pending,
        ]);

        $this->chargePaymentService->syncCharge($payment->charge);

        $this->adminNotifier->notify(new PaymentSubmittedNotification($payment));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж отправлен на рассмотрение.')]);

        return to_route('renter.charges');
    }
}
