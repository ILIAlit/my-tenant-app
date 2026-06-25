<?php

namespace App\Http\Controllers\Admin\Renter;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Renter\RenterCreateRequest;
use App\Http\Requests\Renter\RenterDeleteRequest;
use App\Http\Requests\Renter\RenterUpdateRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminRenterController extends Controller
{
    public function getRenters(): Response
    {
        $renters = User::query()
            ->where('role', UserRole::RENTER)
            ->with('room:id,user_id,type,number,floor')
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/renter', [
            'renters' => $renters,
        ]);
    }

    public function createRenter(RenterCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'login' => $validated['login'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::RENTER,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Арендатор создан.')]);

        return to_route('renters.get');
    }

    public function updateRenter(RenterUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $renter = User::query()
            ->where('role', UserRole::RENTER)
            ->findOrFail($validated['id']);

        $attributes = [
            'name' => $validated['name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'login' => $validated['login'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = $validated['password'];
        }

        $renter->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Арендатор обновлён.')]);

        return back();
    }

    public function deleteRenter(RenterDeleteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            Room::query()
                ->where('user_id', $validated['id'])
                ->update([
                    'user_id' => null,
                    'status' => RoomStatus::Free,
                ]);

            User::query()
                ->where('role', UserRole::RENTER)
                ->whereKey($validated['id'])
                ->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Арендатор удалён.')]);

        return to_route('renters.get');
    }
}
