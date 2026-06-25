<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AdminNotifier
{
    public function notify(Notification $notification): void
    {
        $admins = User::query()
            ->where('role', UserRole::ADMIN->value)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        NotificationFacade::send($admins, $notification);
    }
}
