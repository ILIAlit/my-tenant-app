<?php

namespace App\Http\Controllers\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contracts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractsController extends Controller
{
    public function getRenterContracts(Request $request): Response
    {
        $contracts = Contracts::whereHas('room', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->with('room:id,number')
            ->latest()
            ->get()
            ->append('file_url');

        return Inertia::render('contracts/contracts', [
            'contracts' => $contracts,
        ]);
    }
}
