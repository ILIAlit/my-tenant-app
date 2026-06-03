<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\RoomsCreateRequest;
use App\Http\Requests\Rooms\RoomsUpdateRequest;
use App\Http\Requests\Rooms\RoomsDeleteRequest;
use App\Models\Rooms;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdminRoomsController extends Controller
{
    public function getRooms()
    {
        $rooms = Rooms::all();

        return Inertia::render('admin/rooms', [
            'rooms' => $rooms,
        ]);
    }

    public function createRooms(RoomsCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Rooms::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната успешно добавлена.')]);

        return to_route('rooms.get');
    }

    public function updateRooms(RoomsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);
        $room->update($validated);

        Log::info($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната обновлена.')]);

        return to_route('rooms.get');
    }

    public function deleteRooms(RoomsDeleteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Rooms::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната удалена.')]);

        return to_route('rooms.get');
    }
}
