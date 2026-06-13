<?php

namespace App\Http\Controllers\UtilityReadings;

use App\Enums\UtilityReadingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UtilityReadings\UtilityReadingCreateRequest;
use App\Models\Contracts;
use App\Models\UtilityReading;
use App\Support\UtilityReadingPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class UtilityReadingsController extends Controller
{
    public function index(Request $request): Response
    {
        $rooms = $request->user()
            ->rooms()
            ->orderBy('number')
            ->get(['id', 'number', 'floor', 'square', 'status', 'date_of_last_repair']);

        $roomsUtilityData = UtilityReadingPresenter::forRooms($rooms);

        return Inertia::render('utility-readings/utility-readings', [
            'roomsUtilityData' => $roomsUtilityData,
        ]);
    }

    public function create(UtilityReadingCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $contract = Contracts::findOrFail($validated['contracts_id']);
        $period = $contract->findPeriodByStart($validated['period_start']);

        $existingReading = UtilityReading::query()
            ->where('rooms_id', $validated['rooms_id'])
            ->whereDate('period_start', $period['start'])
            ->first();

        $attributes = [
            'rooms_id' => $validated['rooms_id'],
            'contracts_id' => $validated['contracts_id'],
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'cold_water' => $validated['cold_water'] ?? null,
            'hot_water' => $validated['hot_water'] ?? null,
            'electricity' => $validated['electricity'] ?? null,
            'submitted_by' => $request->user()->id,
            'status' => UtilityReadingStatus::Review,
            'rejection_reason' => null,
        ];

        if ($existingReading !== null) {
            $existingReading->deleteStoredPhotos();
            $existingReading->update([
                ...$attributes,
                'cold_water_photo_path' => $this->storePhoto($request->file('cold_water_photo')),
                'hot_water_photo_path' => $this->storePhoto($request->file('hot_water_photo')),
                'electricity_photo_path' => $this->storePhoto($request->file('electricity_photo')),
            ]);
        } else {
            UtilityReading::create([
                ...$attributes,
                'cold_water_photo_path' => $this->storePhoto($request->file('cold_water_photo')),
                'hot_water_photo_path' => $this->storePhoto($request->file('hot_water_photo')),
                'electricity_photo_path' => $this->storePhoto($request->file('electricity_photo')),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Показания отправлены на проверку.')]);

        return back();
    }

    private function storePhoto(?UploadedFile $file): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file->store('utility-readings', 'public');
    }
}
