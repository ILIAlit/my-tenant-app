<?php

namespace App\Http\Controllers\Admin\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminContractsController extends Controller
{
    public function index(): Response
    {
        $contracts = Contract::query()
            ->with([
                'renter:id,last_name,name,middle_name',
                'renter.room:id,user_id,type,number,floor',
            ])
            ->orderByDesc('start_date')
            ->orderBy('number')
            ->get()
            ->map(fn (Contract $contract): array => [
                'id' => $contract->id,
                'number' => $contract->number,
                'start_date' => $contract->start_date->format('Y-m-d'),
                'end_date' => $contract->end_date?->format('Y-m-d'),
                'monthly_rent' => (float) $contract->monthly_rent,
                'notes' => $contract->notes,
                'file_url' => $contract->fileUrl(),
                'file_name' => $contract->file_path ? basename($contract->file_path) : null,
                'is_image' => $contract->isImage(),
                'renter' => $this->formatRenter($contract->renter),
            ]);

        return Inertia::render('admin/contracts', [
            'contracts' => $contracts,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRenter(User $renter): array
    {
        return [
            'id' => $renter->id,
            'full_name' => trim(implode(' ', array_filter([
                $renter->last_name,
                $renter->name,
                $renter->middle_name,
            ]))),
            'room' => $renter->room ? [
                'type' => $renter->room->type->value,
                'number' => $renter->room->number,
                'floor' => $renter->room->floor,
            ] : null,
        ];
    }
}
