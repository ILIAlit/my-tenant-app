<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\RoomsCreateRequest;
use App\Http\Requests\Rooms\RoomsIdRequest;
use App\Models\Rooms;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;



class AdminRoomsController extends Controller
{

    public function getRooms()
    {
        $rooms = Rooms::all();

        return Inertia::render('admin/rooms/rooms', [
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

    public function deleteRooms(RoomsIdRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Rooms::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната удалена.')]);

        return to_route('rooms.get');
    }
}
