<?php

namespace App\Http\Controllers\Rooms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomsController extends Controller
{
    public function getRenterRooms(Request $request): Response
    {
        $rooms = $request->user()
            ->rooms()
            ->orderBy('number')
            ->get();

        return Inertia::render('rent/rooms', [
            'rooms' => $rooms,
        ]);
    }
}
