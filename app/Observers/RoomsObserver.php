<?php

namespace App\Observers;

use App\Actions\Invoices\MonthlyInvoiceGenerator;
use App\Enums\RoomStatus;
use App\Models\Rooms;

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
            if ($rooms->status !== RoomStatus::FREE->value) {
                $rooms->updateQuietly(['status' => RoomStatus::FREE->value]);
            }

            return;
        }

        if ($rooms->wasChanged('user_id')) {
            app(MonthlyInvoiceGenerator::class)->createForCurrentPeriod($rooms);
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
