<?php

namespace App\Http\Controllers\Admin\Contracts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contracts\ContractCreateRequest;
use App\Http\Requests\Contracts\ContractIdRequest;
use App\Http\Requests\Contracts\ContractUpdateRequest;
use App\Http\Requests\Rooms\RoomsIdRequest;
use App\Models\Contracts;
use App\Models\Rooms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminContractsController extends Controller
{
    public function getAllContracts(): Response
    {
        $contracts = Contracts::with('room:id,number,user_id', 'room.user:id,name,last_name,middle_name')
            ->latest()
            ->get()
            ->append('file_url');

        return Inertia::render('admin/contracts', [
            'contracts' => $contracts,
        ]);
    }

    public function index(RoomsIdRequest $request): Response
    {
        $validated = $request->validated();
        $room = Rooms::findOrFail($validated['id']);

        $contracts = $room->contracts()
            ->latest()
            ->get()
            ->append('file_url');

        return Inertia::render('admin/room-update/contracts', [
            'room' => $room,
            'contracts' => $contracts,
        ]);
    }

    public function create(ContractCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['file_path'] = $request->file('file')->store('contracts', 'public');

        Contracts::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Договор добавлен.')]);

        return to_route('contracts.admin-get', ['id' => $validated['rooms_id']]);
    }

    public function update(ContractUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $contract = Contracts::findOrFail($validated['id']);

        if ($request->hasFile('file')) {
            if ($contract->file_path) {
                Storage::disk('public')->delete($contract->file_path);
            }

            $validated['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $contract->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Договор обновлён.')]);

        return to_route('contracts.admin-get', ['id' => $contract->rooms_id]);
    }

    public function delete(ContractIdRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $contract = Contracts::findOrFail($validated['id']);

        if ($contract->file_path) {
            Storage::disk('public')->delete($contract->file_path);
        }

        $contract->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Договор удалён.')]);

        return to_route('contracts.admin-get', ['id' => $validated['rooms_id']]);
    }
}
