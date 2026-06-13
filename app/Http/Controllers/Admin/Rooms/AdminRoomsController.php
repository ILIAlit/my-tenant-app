<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\RoomsCreateRequest;
use App\Http\Requests\Rooms\RoomsIdRequest;
use App\Models\Rooms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRoomsController extends Controller
{
    public function getRooms(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $allowedStatuses = array_column(RoomStatus::cases(), 'value');
        $status = in_array($status, $allowedStatuses, true) ? $status : '';

        $rooms = Rooms::query()
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('number', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('admin/rooms/rooms', [
            'rooms' => $rooms,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
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
