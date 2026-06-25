<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoomAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Room $room) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'room_assigned',
            'title' => __('Комната назначена'),
            'message' => __('Вам назначена комната :number (этаж :floor).', [
                'number' => $this->room->number,
                'floor' => $this->room->floor,
            ]),
            'url' => '/contract',
        ];
    }
}
