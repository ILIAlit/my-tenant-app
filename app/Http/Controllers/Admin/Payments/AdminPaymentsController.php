<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\PaymentApproveRequest;
use App\Http\Requests\Payments\PaymentRejectRequest;
use App\Models\Invoices;
use App\Models\Payments;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentsController extends Controller
{
    public function getPayments(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $allowedStatuses = array_column(PaymentStatus::cases(), 'value');
        $status = in_array($status, $allowedStatuses, true) ? $status : '';

        $from = $this->normalizeDate((string) $request->query('from', ''));
        $to = $this->normalizeDate((string) $request->query('to', ''));

        $payments = Payments::with('invoice:id,name,total_price,paid_price,user_id', 'invoice.user:id,name,last_name,middle_name')
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($from !== '', function (Builder $query) use ($from): void {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($to !== '', function (Builder $query) use ($to): void {
                $query->whereDate('created_at', '<=', $to);
            })
            ->latest()
            ->get()
            ->append('receipt_url');

        return Inertia::render('admin/payments', [
            'payments' => $payments,
            'filters' => [
                'status' => $status,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function approvePayment(PaymentApproveRequest $request): RedirectResponse
    {
        $this->resolvePayment($request->validated()['id'], function (Payments $payment, Invoices $invoice): void {
            $payment->update(['status' => PaymentStatus::Approved->value]);

            $this->notifyRenter($payment, $invoice);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж одобрен.')]);

        return to_route('payments.admin-get');
    }

    public function rejectPayment(PaymentRejectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->resolvePayment($validated['id'], function (Payments $payment, Invoices $invoice) use ($validated): void {
            $payment->update([
                'status' => PaymentStatus::Rejected->value,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            $invoice->paid_price = max(0, $invoice->paid_price - $payment->amount);
            $invoice->save();

            $this->notifyRenter($payment, $invoice);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Платёж отклонён.')]);

        return to_route('payments.admin-get');
    }

    private function notifyRenter(Payments $payment, Invoices $invoice): void
    {
        $invoice->user?->notify(
            (new PaymentStatusNotification($payment->refresh(), $invoice))->afterCommit()
        );
    }

    private function resolvePayment(int $paymentId, callable $callback): void
    {
        DB::transaction(function () use ($paymentId, $callback): void {
            $payment = Payments::whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Review->value) {
                return;
            }

            $invoice = Invoices::whereKey($payment->invoices_id)->lockForUpdate()->firstOrFail();

            $callback($payment, $invoice);
        });
    }

    /**
     * Возвращает дату в формате Y-m-d, если значение корректно, иначе пустую строку.
     */
    private function normalizeDate(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}
