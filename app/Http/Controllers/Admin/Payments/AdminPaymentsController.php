<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentReviewRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\ChargePaymentService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentsController extends Controller
{
    public function __construct(private ChargePaymentService $chargePaymentService) {}

    public function index(): Response
    {
        $payments = Payment::query()
            ->with([
                'charge:id,user_id,total_amount,created_at',
                'charge.renter:id,last_name,name,middle_name',
            ])
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
                'renter' => [
                    'id' => $payment->charge->renter->id,
                    'full_name' => $this->formatFullName($payment->charge->renter),
                ],
            ]);

        return Inertia::render('admin/payments', [
            'payments' => $payments,
        ]);
    }

    public function approve(PaymentReviewRequest $request): RedirectResponse
    {
        $payment = Payment::query()->findOrFail($request->validated('id'));

        $this->chargePaymentService->approve($payment);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж подтверждён.')]);

        return to_route('payments.get');
    }

    public function reject(PaymentReviewRequest $request): RedirectResponse
    {
        $payment = Payment::query()->findOrFail($request->validated('id'));

        $this->chargePaymentService->reject($payment);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж отклонён.')]);

        return to_route('payments.get');
    }

    private function formatFullName(User $renter): string
    {
        return trim(implode(' ', array_filter([
            $renter->last_name,
            $renter->name,
            $renter->middle_name,
        ])));
    }
}
