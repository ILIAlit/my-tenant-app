<?php

namespace App\Http\Controllers\Renter;

use App\Concerns\FormatsChargeBreakdown;
use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Services\ChargePaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RenterChargesController extends Controller
{
    use FormatsChargeBreakdown;

    public function __construct(private ChargePaymentService $chargePaymentService) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
        ]);

        $createdFrom = $validated['created_from'] ?? null;
        $createdTo = $validated['created_to'] ?? null;

        $charges = Charge::query()
            ->where('user_id', $request->user()->id)
            ->with('payments:id,charge_id,amount,status')
            ->when($createdFrom, fn ($query) => $query->whereDate('created_at', '>=', $createdFrom))
            ->when($createdTo, fn ($query) => $query->whereDate('created_at', '<=', $createdTo))
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Charge $charge): array {
                $remaining = $this->chargePaymentService->remainingAmount($charge);

                return [
                    'id' => $charge->id,
                    'total_amount' => (float) $charge->total_amount,
                    'paid_amount' => (float) $charge->paid_amount,
                    'remaining_amount' => $remaining,
                    'can_pay' => $this->chargePaymentService->canAcceptPayment($charge),
                    'last_payment_date' => $charge->last_payment_date?->format('Y-m-d'),
                    'status' => $charge->status->value,
                    'display_status' => $charge->displayStatus(),
                    'category' => $charge->category->value,
                    'breakdown' => $this->formatChargeBreakdown($charge),
                    'created_at' => $charge->created_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('renter/charges', [
            'charges' => $charges,
            'filters' => [
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ],
        ]);
    }
}
