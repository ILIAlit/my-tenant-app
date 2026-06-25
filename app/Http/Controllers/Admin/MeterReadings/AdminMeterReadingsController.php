<?php

namespace App\Http\Controllers\Admin\MeterReadings;

use App\Enums\MeterReadingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeterReading\MeterReadingCreateRequest;
use App\Http\Requests\MeterReading\MeterReadingDestroyRequest;
use App\Http\Requests\MeterReading\MeterReadingReviewRequest;
use App\Http\Requests\MeterReading\MeterReadingUpdateRequest;
use App\Http\Requests\MeterReading\MeterTariffUpdateRequest;
use App\Models\MeterReading;
use App\Models\User;
use App\Notifications\MeterReadingApprovedNotification;
use App\Notifications\MeterReadingRejectedNotification;
use App\Services\MeterReadingBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminMeterReadingsController extends Controller
{
    public function __construct(private MeterReadingBillingService $billingService) {}

    public function index(Request $request): Response
    {
        $showArchive = $request->boolean('archive');

        if ($showArchive) {
            $meterReadings = MeterReading::query()
                ->archived()
                ->where('is_initial', false)
                ->orderByDesc('reading_date')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (MeterReading $reading): array => [
                    'id' => $reading->id,
                    'user_id' => $reading->user_id,
                    'type' => $reading->type->value,
                    'reading_date' => $reading->reading_date->format('Y-m-d'),
                    'value' => (float) $reading->value,
                    'status' => $reading->status->value,
                    'previous_value' => null,
                    'consumption' => $reading->consumption !== null ? (float) $reading->consumption : null,
                    'estimated_cost' => $reading->charged_amount !== null ? (float) $reading->charged_amount : null,
                    'renter' => $this->formatRenterForReading($reading),
                ]);
        } else {
            $readings = MeterReading::query()
                ->active()
                ->where('is_initial', false)
                ->with([
                    'renter:id,last_name,name,middle_name',
                    'renter.room:id,user_id,type,number,floor',
                ])
                ->orderByDesc('reading_date')
                ->orderByDesc('created_at')
                ->get();

            $history = MeterReading::query()
                ->forConsumption()
                ->whereIn('user_id', $readings->pluck('user_id')->unique())
                ->get();

            $billing = $this->billingService->enrichWithBilling($readings, $history);

            $meterReadings = $readings->map(function (MeterReading $reading) use ($billing): array {
                $data = $billing[$reading->id];
                $showConsumption = in_array($reading->status, [
                    MeterReadingStatus::Approved,
                    MeterReadingStatus::Pending,
                ], true);

                return [
                    'id' => $reading->id,
                    'user_id' => $reading->user_id,
                    'type' => $reading->type->value,
                    'reading_date' => $reading->reading_date->format('Y-m-d'),
                    'value' => (float) $reading->value,
                    'status' => $reading->status->value,
                    'previous_value' => $showConsumption ? $data['previous_value'] : null,
                    'consumption' => $showConsumption ? $data['consumption'] : null,
                    'estimated_cost' => $showConsumption ? $data['estimated_cost'] : null,
                    'renter' => $this->formatRenter($reading->renter),
                ];
            });
        }

        $renters = User::query()
            ->where('role', UserRole::RENTER)
            ->orderBy('last_name')
            ->orderBy('name')
            ->get(['id', 'last_name', 'name', 'middle_name'])
            ->map(fn (User $renter): array => [
                'id' => $renter->id,
                'full_name' => $this->formatFullName($renter),
            ]);

        return Inertia::render('admin/meter-readings', [
            'meterReadings' => $meterReadings,
            'renters' => $renters,
            'tariffs' => $this->billingService->tariffsGroupedByRoomType(),
            'showArchive' => $showArchive,
        ]);
    }

    public function updateTariffs(MeterTariffUpdateRequest $request): RedirectResponse
    {
        $this->billingService->updateTariffs($request->validated('tariffs'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Тарифы сохранены.')]);

        return to_route('meter-readings.get');
    }

    public function store(MeterReadingCreateRequest $request): RedirectResponse
    {
        $reading = MeterReading::create([
            ...$request->validated(),
            'is_initial' => false,
            'status' => MeterReadingStatus::Approved,
        ]);

        $reading->load('renter');
        $this->billingService->billApprovedReading($reading->fresh());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показание счётчика добавлено.')]);

        return to_route('meter-readings.get');
    }

    public function update(MeterReadingUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $reading = MeterReading::query()->findOrFail($validated['id']);
        $reading->update([
            ...collect($validated)->except('id')->all(),
            'status' => MeterReadingStatus::Approved,
        ]);

        $this->billingService->billApprovedReading($reading->fresh());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показание счётчика обновлено.')]);

        return to_route('meter-readings.get');
    }

    public function approve(MeterReadingReviewRequest $request): RedirectResponse
    {
        $reading = MeterReading::query()
            ->with('renter')
            ->findOrFail($request->validated('id'));

        $reading->update(['status' => MeterReadingStatus::Approved]);
        $charge = $this->billingService->billApprovedReading($reading->fresh());

        $reading->renter->notify(new MeterReadingApprovedNotification($reading));

        $message = $charge !== null
            ? __('Показание подтверждено. Начисление за коммунальные услуги: :amount BYN.', [
                'amount' => number_format((float) $charge->total_amount, 2, '.', ''),
            ])
            : __('Показание подтверждено.');

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('meter-readings.get');
    }

    public function reject(MeterReadingReviewRequest $request): RedirectResponse
    {
        $reading = MeterReading::query()
            ->with('renter')
            ->findOrFail($request->validated('id'));

        $reading->update(['status' => MeterReadingStatus::Rejected]);
        $reading->renter->notify(new MeterReadingRejectedNotification($reading));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показание отклонено.')]);

        return to_route('meter-readings.get');
    }

    public function destroy(MeterReadingDestroyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        MeterReading::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показание счётчика удалено.')]);

        return to_route('meter-readings.get');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRenterForReading(MeterReading $reading): array
    {
        if ($reading->renter !== null) {
            return $this->formatRenter($reading->renter);
        }

        return [
            'id' => null,
            'full_name' => $reading->archived_renter_name ?? '—',
            'room_label' => $reading->archived_room_label,
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
