<?php

namespace App\Http\Controllers\Admin\Amenities;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Amenities;
use App\Models\Rooms;
use App\Http\Requests\Amenities\AmenitiesCreateRequest;
use App\Http\Requests\Amenities\AmenitiesDeleteRequest;
use App\Http\Requests\Rooms\RoomsIdRequest;

class AdminAmenitiesController extends Controller
{
    public function index(RoomsIdRequest $request)
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated["id"]);
        $amenities = $room->amenities()->get();

        return Inertia::render('admin/room-update/add-amenities', [
            'room' => $room,
            'amenities' => $amenities,
        ]);
    }

    public function create(AmenitiesCreateRequest $request)
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['rooms_id']);
        $amenities = $room->amenities()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Услуга создана.')]);

        return to_route('amenities.get', ['id' => $validated['rooms_id']]);
    }

    public function delete(AmenitiesDeleteRequest $request)
    {
        $validated = $request->validated();
        Amenities::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Услуга удалена.')]);

        return to_route('amenities.get', ['id' => $validated['rooms_id']]);
    }
}
