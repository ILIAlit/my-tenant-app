<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Enums\UserRole;

class AdminController extends Controller
{
    public function updateRenters(Request $request)
    {
        $renters = User::where('role', UserRole::RENTER->value)->get();

        return Inertia::render('admin/renter', [
            'users' => $renters,
        ]);
    }
}
