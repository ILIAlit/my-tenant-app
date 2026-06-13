<?php

namespace App\Http\Controllers\Admin\UtilityReadings;

use App\Actions\UtilityReadings\UtilityReadingBiller;
use App\Enums\UtilityReadingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\RoomsIdRequest;
use App\Http\Requests\UtilityReadings\UtilityReadingApproveRequest;
use App\Http\Requests\UtilityReadings\UtilityReadingIdRequest;
use App\Http\Requests\UtilityReadings\UtilityReadingRejectRequest;
use App\Http\Requests\UtilityReadings\UtilityReadingUpdateRequest;
use App\Http\Requests\UtilityReadings\UtilityTariffUpdateRequest;
use App\Models\Rooms;
use App\Models\UtilityReading;
use App\Models\UtilityTariff;
use App\Notifications\UtilityReadingStatusNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminUtilityReadingsController extends Controller
{
    public function getAllReadings(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $allowedStatuses = array_column(UtilityReadingStatus::cases(), 'value');
        $status = in_array($status, $allowedStatuses, true) ? $status : '';

        $readings = UtilityReading::query()
            ->with([
                'room:id,number,user_id',
                'room.user:id,name,last_name,middle_name',
                'contract:id,number',
                'submitter:id,name,last_name,middle_name',
            ])
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->latest('period_start')
            ->get()
            ->each(fn (UtilityReading $reading) => $reading->append([
                'cold_water_photo_url',
                'hot_water_photo_url',
                'electricity_photo_url',
            ]));

        return Inertia::render('admin/utility-readings', [
            'readings' => $readings,
            'tariffs' => UtilityTariff::current(),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function updateTariffs(UtilityTariffUpdateRequest $request): RedirectResponse
    {
        $tariff = UtilityTariff::current();
        $tariff->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Тарифы обновлены.')]);

        return to_route('utility-readings.all-get');
    }

    public function index(RoomsIdRequest $request): Response
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);

        $readings = $room->utilityReadings()
            ->with('contract:id,number', 'submitter:id,name,last_name,middle_name')
            ->latest('period_start')
            ->get()
            ->each(fn (UtilityReading $reading) => $reading->append([
                'cold_water_photo_url',
                'hot_water_photo_url',
                'electricity_photo_url',
            ]));

        return Inertia::render('admin/room-update/utility-readings', [
            'room' => $room,
            'readings' => $readings,
        ]);
    }

    public function update(UtilityReadingUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $reading = UtilityReading::findOrFail($validated['id']);

        $reading->update([
            'cold_water' => $validated['cold_water'] ?? null,
            'hot_water' => $validated['hot_water'] ?? null,
            'electricity' => $validated['electricity'] ?? null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показания обновлены.')]);

        if ($request->boolean('from_room')) {
            return to_route('utility-readings.admin-get', ['id' => $reading->rooms_id]);
        }

        return to_route('utility-readings.all-get');
    }

    public function approve(
        UtilityReadingApproveRequest $request,
        UtilityReadingBiller $biller,
    ): RedirectResponse {
        $billingResult = null;

        try {
            $this->resolveReading((int) $request->validated()['id'], function (UtilityReading $reading) use ($biller, &$billingResult): void {
                $tariff = UtilityTariff::current();
                $billingResult = $biller->bill($reading, $tariff);

                $reading->update([
                    'status' => UtilityReadingStatus::Approved,
                    'rejection_reason' => null,
                    'cold_water_consumption' => $billingResult['cold_water_consumption'],
                    'hot_water_consumption' => $billingResult['hot_water_consumption'],
                    'electricity_consumption' => $billingResult['electricity_consumption'],
                    'utility_amount' => $billingResult['utility_amount'],
                    'invoices_id' => $billingResult['invoices_id'],
                ]);

                $this->notifyRenter($reading);
            });
        } catch (\RuntimeException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return to_route('utility-readings.all-get');
        }

        $message = $billingResult !== null && $billingResult['utility_amount'] > 0
            ? __('Показания одобрены. Начислено :amount ₽ за коммунальные услуги.', [
                'amount' => number_format($billingResult['utility_amount'], 0, '.', ' '),
            ])
            : __('Показания одобрены.');

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('utility-readings.all-get');
    }

    public function reject(UtilityReadingRejectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->resolveReading((int) $validated['id'], function (UtilityReading $reading) use ($validated): void {
            $reading->update([
                'status' => UtilityReadingStatus::Rejected,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            $this->notifyRenter($reading);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показания отклонены.')]);

        return to_route('utility-readings.all-get');
    }

    public function delete(UtilityReadingIdRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $reading = UtilityReading::findOrFail($validated['id']);
        $reading->deleteStoredPhotos();
        $reading->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показания удалены.')]);

        return to_route('utility-readings.admin-get', ['id' => $validated['rooms_id']]);
    }

    private function notifyRenter(UtilityReading $reading): void
    {
        $reading->loadMissing('room.user');

        $reading->room?->user?->notify(
            (new UtilityReadingStatusNotification($reading))->afterCommit()
        );
    }

    private function resolveReading(int $readingId, callable $callback): void
    {
        DB::transaction(function () use ($readingId, $callback): void {
            $reading = UtilityReading::whereKey($readingId)->lockForUpdate()->firstOrFail();

            if ($reading->status !== UtilityReadingStatus::Review) {
                return;
            }

            $callback($reading);
        });
    }
}
