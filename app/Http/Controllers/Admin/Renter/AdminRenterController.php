<?php

namespace App\Http\Controllers\Admin\Renter;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserIdRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRenterController extends Controller
{
    public function getRenters(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $status = in_array($status, ['none', 'pending', 'paid', 'debt'], true) ? $status : '';

        $renters = User::where('role', UserRole::RENTER->value)
            ->withSum('invoices as invoices_total', 'total_price')
            ->withSum('invoices as invoices_paid', 'paid_price')
            ->with(['invoices' => function ($query): void {
                $query->withExists(['payments as has_pending_payment' => function (Builder $payments): void {
                    $payments->where('status', PaymentStatus::Review->value);
                }]);
            }])
            ->when($search !== '', function (Builder $query) use ($search): void {
                foreach (preg_split('/\s+/', $search) as $term) {
                    $query->where(function (Builder $sub) use ($term): void {
                        $sub->where('last_name', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%");
                    });
                }
            })
            ->get();

        $renters->each(function (User $renter): void {
            $renter->payment_status = $this->resolveRenterStatus($renter);
            $renter->unsetRelation('invoices');
        });

        if ($status !== '') {
            $renters = $renters->where('payment_status', $status)->values();
        }

        return Inertia::render('admin/renter', [
            'renters' => $renters,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Платёжный статус арендатора по его начислениям:
     * нет начислений, есть просроченный долг, ожидает оплаты или всё оплачено.
     */
    private function resolveRenterStatus(User $renter): string
    {
        $invoices = $renter->invoices;

        if ($invoices->sum('total_price') <= 0) {
            return 'none';
        }

        $hasDebt = $invoices->contains(
            fn ($invoice): bool => $invoice->current_status === InvoiceStatus::Debt->value
        );

        if ($hasDebt) {
            return 'debt';
        }

        $outstanding = $invoices->sum(
            fn ($invoice): int => max(0, $invoice->total_price - $invoice->paid_price)
        );

        return $outstanding > 0 ? 'pending' : 'paid';
    }

    public function updateRenters(UserUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $renter = User::findOrFail($validated['id']);
        $renter->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Арендатор обновлён.')]);

        return to_route('renters.get');
    }

    public function deleteRenters(UserIdRequest $request): RedirectResponse
    {
        User::destroy($request->validated()['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Арендатор удалён.')]);

        return to_route('renters.get');
    }
}
