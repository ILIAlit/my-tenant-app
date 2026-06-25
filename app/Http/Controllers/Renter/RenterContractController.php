<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RenterContractController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $user->load([
            'contract',
            'room:id,user_id,type,number,floor,area',
        ]);

        return Inertia::render('renter/contract', [
            'contract' => $user->contract
                ? $this->formatContract($user->contract)
                : null,
            'room' => $user->room
                ? $this->formatRoom($user->room)
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRoom(Room $room): array
    {
        return [
            'type' => $room->type->value,
            'number' => $room->number,
            'floor' => $room->floor,
            'area' => (float) $room->area,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatContract(Contract $contract): array
    {
        return [
            'id' => $contract->id,
            'number' => $contract->number,
            'start_date' => $contract->start_date->format('Y-m-d'),
            'end_date' => $contract->end_date?->format('Y-m-d'),
            'monthly_rent' => (float) $contract->monthly_rent,
            'notes' => $contract->notes,
            'file_url' => $contract->fileUrl(),
            'file_name' => $contract->file_path ? basename($contract->file_path) : null,
            'is_image' => $contract->isImage(),
        ];
    }
}
