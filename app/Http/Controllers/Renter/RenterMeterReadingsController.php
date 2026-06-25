<?php

namespace App\Http\Controllers\Renter;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeterReading\MeterReadingStoreRequest;
use App\Models\MeterReading;
use App\Notifications\MeterReadingSubmittedNotification;
use App\Services\AdminNotifier;
use App\Services\MeterReadingBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RenterMeterReadingsController extends Controller
{
    public function __construct(
        private MeterReadingBillingService $billingService,
        private AdminNotifier $adminNotifier,
    ) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'reading_from' => ['nullable', 'date'],
            'reading_to' => ['nullable', 'date', 'after_or_equal:reading_from'],
            'type' => ['nullable', 'string', Rule::in(MeterType::meteredValues())],
        ]);

        $readingFrom = $validated['reading_from'] ?? null;
        $readingTo = $validated['reading_to'] ?? null;
        $type = $validated['type'] ?? null;

        $history = MeterReading::query()
            ->where('user_id', $request->user()->id)
            ->forConsumption()
            ->get();

        $readings = MeterReading::query()
            ->where('user_id', $request->user()->id)
            ->where('is_initial', false)
            ->when($readingFrom, fn ($query) => $query->whereDate('reading_date', '>=', $readingFrom))
            ->when($readingTo, fn ($query) => $query->whereDate('reading_date', '<=', $readingTo))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderByDesc('reading_date')
            ->orderByDesc('created_at')
            ->get();

        $billing = $this->billingService->enrichWithBilling($readings, $history);

        $meterReadings = $readings->map(function (MeterReading $reading) use ($billing): array {
            $data = $billing[$reading->id];
            $isApproved = $reading->status === MeterReadingStatus::Approved;

            return [
                'id' => $reading->id,
                'type' => $reading->type->value,
                'reading_date' => $reading->reading_date->format('Y-m-d'),
                'value' => (float) $reading->value,
                'status' => $reading->status->value,
                'previous_value' => $isApproved ? $data['previous_value'] : null,
                'consumption' => $isApproved ? $data['consumption'] : null,
                'charged_amount' => $isApproved ? $data['estimated_cost'] : null,
            ];
        });

        return Inertia::render('renter/meter-readings', [
            'meterReadings' => $meterReadings,
            'filters' => [
                'reading_from' => $readingFrom,
                'reading_to' => $readingTo,
                'type' => $type,
            ],
        ]);
    }

    public function store(MeterReadingStoreRequest $request): RedirectResponse
    {
        $reading = MeterReading::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'is_initial' => false,
            'status' => MeterReadingStatus::Pending,
        ]);

        $this->adminNotifier->notify(new MeterReadingSubmittedNotification($reading));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показание отправлено на подтверждение.')]);

        return to_route('renter.meter-readings');
    }
}
