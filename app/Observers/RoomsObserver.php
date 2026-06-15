<?php

namespace App\Observers;

use App\Models\Rooms;
use App\Models\Invoices;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoomsObserver
{
    /**
     * Handle the Rooms "created" event.
     */
    public function created(Rooms $rooms): void
    {
        //
    }

    /**
     * Handle the Rooms "updated" event.
     */
    public function updated(Rooms $rooms): void
    {
        if (is_null($rooms->user_id)) {
        }
        if ($rooms->wasChanged('user_id') && !is_null($rooms->user_id)) {

            $amenities = $rooms->amenities;
            $totalPrice = $amenities->sum('price');
            Invoices::create([
                'user_id' => $rooms->user_id,
                'name' => "Начисление за комнату № " . $rooms->number,
                'total_price' => $totalPrice,
                'due_date' => now()->addMonths()->format('d.m.Y'),
                'create_date' => now()->format('d.m.Y'),
            ]);
            foreach ($amenities as $amenitiesItem) {
            }
            Log::info($amenities);
        }
    }



    /**
     * Handle the Rooms "deleted" event.
     */
    public function deleted(Rooms $rooms): void
    {
        //
    }

    /**
     * Handle the Rooms "restored" event.
     */
    public function restored(Rooms $rooms): void
    {
        //
    }

    /**
     * Handle the Rooms "force deleted" event.
     */
    public function forceDeleted(Rooms $rooms): void
    {
        //
    }
}
