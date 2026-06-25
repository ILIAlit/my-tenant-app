<?php

namespace App\Services;

use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\Room;
use App\Models\User;

class RenterAssignmentArchiveService
{
    public function archiveForRoomChange(User $renter): void
    {
        $renter->load('room');

        $renterName = $this->formatFullName($renter);
        $roomLabel = $this->roomArchiveLabel($renter->room);

        Charge::query()
            ->where('user_id', $renter->id)
            ->where('status', '!=', ChargeStatus::Archived)
            ->update([
                'status' => ChargeStatus::Archived,
                'archived_renter_name' => $renterName,
                'archived_room_label' => $roomLabel,
                'user_id' => null,
            ]);

        MeterReading::query()
            ->where('user_id', $renter->id)
            ->where('status', '!=', MeterReadingStatus::Archived)
            ->update([
                'status' => MeterReadingStatus::Archived,
                'archived_renter_name' => $renterName,
                'archived_room_label' => $roomLabel,
                'user_id' => null,
            ]);
    }

    private function formatFullName(User $renter): string
    {
        return trim(implode(' ', array_filter([
            $renter->last_name,
            $renter->name,
            $renter->middle_name,
        ])));
    }

    private function roomArchiveLabel(?Room $room): ?string
    {
        if ($room === null) {
            return null;
        }

        $label = $room->type->label().' '.$room->number;

        if ($room->floor !== null) {
            $label .= ' (эт. '.$room->floor.')';
        }

        return $label;
    }
}
