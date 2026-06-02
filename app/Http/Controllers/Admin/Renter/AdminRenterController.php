<?php

namespace App\Http\Controllers\Admin\Renter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Enums\UserRole;

class AdminRenterController extends Controller
{
    public function getRenters()
    {
        $renters = User::where('role', UserRole::RENTER)->get();

        return Inertia::render('admin/renter', [
            'users' => $renters,
        ]);
    }

    public function deleteRenters(Request $request)
    {
        $renterId = $request->id;
        User::destroy($renterId);
        $renters = User::where('role', UserRole::RENTER->value)->get();

        return Inertia::render('admin/renter', [
            'users' => $renters,
        ]);
    }
}
