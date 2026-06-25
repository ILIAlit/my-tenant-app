<?php

namespace App\Http\Controllers\Admin\Charges;

use App\Concerns\FormatsChargeBreakdown;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Charge\ChargeCreateRequest;
use App\Http\Requests\Charge\ChargeDestroyRequest;
use App\Http\Requests\Charge\ChargeUpdateRequest;
use App\Models\Charge;
use App\Models\User;
use App\Notifications\ChargeCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminChargesController extends Controller
{
    use FormatsChargeBreakdown;

    public function index(Request $request): Response
    {
        $showArchive = $request->boolean('archive');

        $query = Charge::query()
            ->orderByDesc('last_payment_date')
            ->orderByDesc('created_at');

        if ($showArchive) {
            $query->archived();
        } else {
            $query->active()
                ->whereNotNull('user_id')
                ->with([
                    'renter:id,last_name,name,middle_name',
                    'renter.room:id,user_id,type,number,floor',
                ]);
        }

        $charges = $query
            ->get()
            ->map(fn (Charge $charge): array => [
                'id' => $charge->id,
                'user_id' => $charge->user_id,
                'total_amount' => (float) $charge->total_amount,
                'paid_amount' => (float) $charge->paid_amount,
                'last_payment_date' => $charge->last_payment_date?->format('Y-m-d'),
                'status' => $charge->status->value,
                'display_status' => $charge->displayStatus(),
                'category' => $charge->category->value,
                'breakdown' => $this->formatChargeBreakdown($charge),
                'renter' => $this->formatRenterForCharge($charge),
            ]);

        $renters = User::query()
            ->where('role', UserRole::RENTER)
            ->orderBy('last_name')
            ->orderBy('name')
            ->get(['id', 'last_name', 'name', 'middle_name'])
            ->map(fn (User $renter): array => [
                'id' => $renter->id,
                'full_name' => $this->formatFullName($renter),
            ]);

        return Inertia::render('admin/charges', [
            'charges' => $charges,
            'renters' => $renters,
            'showArchive' => $showArchive,
        ]);
    }

    public function store(ChargeCreateRequest $request): RedirectResponse
    {
        $charge = Charge::create($request->validated());
        $charge->renter->notify(new ChargeCreatedNotification($charge));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление создано.')]);

        return to_route('charges.get');
    }

    public function update(ChargeUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Charge::query()
            ->findOrFail($validated['id'])
            ->update(collect($validated)->except('id')->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление обновлено.')]);

        return to_route('charges.get');
    }

    public function destroy(ChargeDestroyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Charge::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление удалено.')]);

        return to_route('charges.get');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRenterForCharge(Charge $charge): array
    {
        if ($charge->renter !== null) {
            return $this->formatRenter($charge->renter);
        }

        return [
            'id' => null,
            'full_name' => $charge->archived_renter_name ?? '—',
            'room_label' => $charge->archived_room_label,
            'room' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRenter(User $renter): array
    {
        return [
            'id' => $renter->id,
            'full_name' => $this->formatFullName($renter),
            'room' => $renter->room ? [
                'type' => $renter->room->type->value,
                'number' => $renter->room->number,
                'floor' => $renter->room->floor,
            ] : null,
        ];
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
