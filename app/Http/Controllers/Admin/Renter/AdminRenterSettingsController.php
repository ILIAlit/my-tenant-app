<?php

namespace App\Http\Controllers\Admin\Renter;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Renter\RenterAssignRoomRequest;
use App\Http\Requests\Renter\RenterContractRequest;
use App\Http\Requests\Renter\RenterInitialMeterReadingsRequest;
use App\Http\Requests\Renter\RenterServiceCreateRequest;
use App\Http\Requests\Renter\RenterServiceDeleteRequest;
use App\Models\Contract;
use App\Models\MeterReading;
use App\Models\RenterService;
use App\Models\Room;
use App\Models\User;
use App\Notifications\RoomAssignedNotification;
use App\Services\RenterAssignmentArchiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminRenterSettingsController extends Controller
{
    public function __construct(
        private RenterAssignmentArchiveService $archiveService,
    ) {}

    public function showSettings(int $id): Response
    {
        $renter = User::query()
            ->where('role', UserRole::RENTER)
            ->with([
                'room:id,user_id,type,number,floor',
                'contract',
                'renterServices' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($id);

        $rooms = Room::query()
            ->orderBy('floor')
            ->orderBy('number')
            ->get(['id', 'type', 'number', 'floor', 'status', 'user_id'])
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'type' => $room->type->value,
                'number' => $room->number,
                'floor' => $room->floor,
                'status' => $room->status->value,
                'user_id' => $room->user_id,
            ]);

        return Inertia::render('admin/renter/settings', [
            'renter' => $renter,
            'rooms' => $rooms,
            'contract' => $renter->contract ? $this->formatContract($renter->contract) : null,
            'services' => $renter->renterServices->map(fn (RenterService $service): array => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'is_active' => $service->is_active,
                'notes' => $service->notes,
            ]),
            'initialMeterReadings' => $this->formatInitialMeterReadings($renter->id),
        ]);
    }

    public function assignRoom(RenterAssignRoomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $renter = User::query()
            ->where('role', UserRole::RENTER)
            ->findOrFail($validated['id']);

        DB::transaction(function () use ($renter, $validated): void {
            $currentRoomId = Room::query()
                ->where('user_id', $renter->id)
                ->value('id');

            $newRoomId = $validated['room_id'] ?? null;

            if ($currentRoomId !== null && $currentRoomId !== $newRoomId) {
                $renter->renterServices()->delete();
                $this->archiveService->archiveForRoomChange($renter);
            }

            Room::query()
                ->where('user_id', $renter->id)
                ->update([
                    'user_id' => null,
                    'status' => RoomStatus::Free,
                ]);

            if ($newRoomId === null) {
                return;
            }

            Room::query()
                ->findOrFail($newRoomId)
                ->update([
                    'user_id' => $renter->id,
                    'status' => RoomStatus::Occupied,
                ]);
        });

        if (($validated['room_id'] ?? null) !== null) {
            $room = Room::query()->findOrFail($validated['room_id']);
            $renter->notify(new RoomAssignedNotification($room));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната назначена.')]);

        return to_route('renters.settings', $renter->id);
    }

    public function upsertContract(RenterContractRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $renterId = $validated['id'];

        $contract = Contract::query()->firstOrNew(['user_id' => $renterId]);

        $attributes = [
            'number' => $validated['number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'monthly_rent' => $validated['monthly_rent'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->boolean('remove_file') && $contract->file_path !== null) {
            Storage::disk('public')->delete($contract->file_path);
            $attributes['file_path'] = null;
        }

        if ($request->hasFile('file')) {
            if ($contract->file_path !== null) {
                Storage::disk('public')->delete($contract->file_path);
            }

            $attributes['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $contract->fill($attributes);
        $contract->user_id = $renterId;
        $contract->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Договор сохранён.')]);

        return to_route('renters.settings', $renterId);
    }

    public function storeService(RenterServiceCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $renterId = $validated['id'];

        RenterService::create([
            'user_id' => $renterId,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'notes' => $validated['notes'] ?? null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Услуга добавлена.')]);

        return to_route('renters.settings', $renterId);
    }

    public function destroyService(RenterServiceDeleteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        RenterService::query()
            ->whereKey($validated['service_id'])
            ->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Услуга удалена.')]);

        return to_route('renters.settings', $validated['id']);
    }

    public function upsertInitialMeterReadings(RenterInitialMeterReadingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $renterId = $validated['id'];
        $readings = $validated['readings'] ?? [];

        foreach (MeterType::metered() as $type) {
            $payload = $readings[$type->value] ?? null;
            $value = $payload['value'] ?? null;
            $readingDate = $payload['reading_date'] ?? null;

            $existing = MeterReading::query()
                ->where('user_id', $renterId)
                ->where('type', $type)
                ->where('is_initial', true)
                ->first();

            if ($value === null || $value === '' || $readingDate === null || $readingDate === '') {
                $existing?->delete();

                continue;
            }

            if ($existing !== null) {
                $existing->update([
                    'value' => $value,
                    'reading_date' => $readingDate,
                    'status' => MeterReadingStatus::Approved,
                ]);

                continue;
            }

            MeterReading::create([
                'user_id' => $renterId,
                'type' => $type,
                'reading_date' => $readingDate,
                'value' => $value,
                'is_initial' => true,
                'status' => MeterReadingStatus::Approved,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начальные показания сохранены.')]);

        return to_route('renters.settings', $renterId);
    }

    /**
     * @return array<string, array{value: float, reading_date: string}|null>
     */
    private function formatInitialMeterReadings(int $renterId): array
    {
        $readings = MeterReading::query()
            ->where('user_id', $renterId)
            ->where('is_initial', true)
            ->get()
            ->keyBy(fn (MeterReading $reading) => $reading->type->value);

        $result = [];

        foreach (MeterType::metered() as $type) {
            $reading = $readings->get($type->value);

            $result[$type->value] = $reading ? [
                'value' => (float) $reading->value,
                'reading_date' => $reading->reading_date->format('Y-m-d'),
            ] : null;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatContract(Contract $contract): array
    {
        return [
            'id' => $contract->id,
            'number' => $contract->number,
            'start_date' => $contract->start_date->format('Y-m-d'),
            'end_date' => $contract->end_date?->format('Y-m-d'),
            'monthly_rent' => (float) $contract->monthly_rent,
            'notes' => $contract->notes,
            'file_url' => $contract->fileUrl(),
            'file_name' => $contract->file_path ? basename($contract->file_path) : null,
            'is_image' => $contract->isImage(),
        ];
    }
}
