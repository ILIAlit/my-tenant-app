<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\AddRenterToRoomRequest;
use App\Http\Requests\Rooms\RoomsIdRequest;
use App\Http\Requests\Rooms\RoomsUpdateRequest;
use App\Models\Rooms;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AdminEditRoomsController extends Controller
{
    public function addRenterToRoom(AddRenterToRoomRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['room_id']);

        if ($room->amenities()->doesntExist() || $room->contracts()->doesntExist()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Добавьте хотя бы одну услугу и договор перед назначением арендатора.')]);

            return to_route('rooms.get-add-renter-to-room', ['id' => $room->id]);
        }

        $room->update(['user_id' => $validated['renter_id'], 'status' => RoomStatus::USED->value]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Пользователь добавлен к комнате.')]);

        return to_route('rooms.get-add-renter-to-room', ['id' => $room->id]);
    }

    public function deleteRenterFromRoom(RoomsIdRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);
        $room->update(['user_id' => null, 'status' => RoomStatus::FREE->value]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Пользователь удалён с комнаты.')]);

        return to_route('rooms.get-add-renter-to-room', ['id' => $validated['id']]);
    }

    public function getUpdateRooms(RoomsIdRequest $request)
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);

        return Inertia::render('admin/room-update/update', [
            'room' => $room,
        ]);
    }

    public function getAddRenterToRoom(RoomsIdRequest $request)
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);
        $renters = User::where('role', UserRole::RENTER)->get();

        return Inertia::render('admin/room-update/add-renter', [
            'room' => $room,
            'renters' => $renters,
            'hasAmenities' => $room->amenities()->exists(),
            'hasContracts' => $room->contracts()->exists(),
        ]);
    }

    public function updateRooms(RoomsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);
        $room->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Комната обновлена.')]);

        return to_route('rooms.get-update', ['id' => $validated['id']]);
    }
}
