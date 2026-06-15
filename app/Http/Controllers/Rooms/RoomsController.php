<?php

namespace App\Http\Controllers\Rooms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\UserIdRequest;
use Illuminate\Support\Facades\Log;
use App\Models\Rooms;
use Inertia\Inertia;

class RoomsController extends Controller
{
    public function getRenterRooms(Request $request)
    {
        $user = $request->user();
        $rooms = $user->rooms;

        return Inertia::render('rent/rooms', [
            'rooms' => $rooms,
        ]);
    }
}
