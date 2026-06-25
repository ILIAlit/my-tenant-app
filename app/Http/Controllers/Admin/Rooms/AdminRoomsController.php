<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomCreateRequest;
use App\Http\Requests\Room\RoomDeleteRequest;
use App\Http\Requests\Room\RoomUpdateRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminRoomsController extends Controller
{
    public function index(): Response
    {
        $rooms = Room::query()
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'type' => $room->type->value,
                'number' => $room->number,
                'floor' => $room->floor,
                'area' => (float) $room->area,
                'status' => $room->status->value,
                'last_repair_date' => $room->last_repair_date?->format('Y-m-d'),
                'notes' => $room->notes,
            ]);

        return Inertia::render('admin/rooms', [
            'rooms' => $rooms,
        ]);
    }

    public function store(RoomCreateRequest $request): RedirectResponse
    {
        $room = Room::create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':type создан.', ['type' => $room->type->label()]),
        ]);

        return to_route('rooms.get');
    }

    public function update(RoomUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $room = Room::query()->findOrFail($validated['id']);
        $room->update(collect($validated)->except('id')->all());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':type обновлён.', ['type' => $room->type->label()]),
        ]);

        return to_route('rooms.get');
    }

    public function destroy(RoomDeleteRequest $request): RedirectResponse
    {
        $room = Room::query()->findOrFail($request->validated('id'));
        $typeLabel = $room->type->label();

        $room->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':type удалён.', ['type' => $typeLabel]),
        ]);

        return to_route('rooms.get');
    }
}
