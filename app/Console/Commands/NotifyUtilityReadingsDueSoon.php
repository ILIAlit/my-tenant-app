<?php

namespace App\Console\Commands;

use App\Enums\UtilityReadingStatus;
use App\Models\Rooms;
use App\Notifications\UtilityReadingDueSoonNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NotifyUtilityReadingsDueSoon extends Command
{
    protected $signature = 'utility-readings:notify-due-soon
                            {--days=3 : За сколько дней до конца периода отправлять напоминание}';

    protected $description = 'Уведомляет арендаторов о необходимости передать показания счётчиков';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $today = CarbonImmutable::now()->startOfDay();
        $sent = 0;

        $rooms = Rooms::query()
            ->whereNotNull('user_id')
            ->with([
                'user',
                'contracts' => fn ($query) => $query->latest('conclusion_date'),
            ])
            ->get();

        foreach ($rooms as $room) {
            if ($room->user === null) {
                continue;
            }

            $contract = $room->contracts->first();

            if ($contract === null) {
                continue;
            }

            foreach ($contract->billingPeriods() as $period) {
                if ((int) $today->diffInDays($period['end'], false) !== $days) {
                    continue;
                }

                if ($this->readingExists($room, $period['start'])) {
                    continue;
                }

                if ($this->alreadyNotified($room, $period['start'])) {
                    continue;
                }

                $room->user->notify(new UtilityReadingDueSoonNotification($room, $period, $days));
                $sent++;
            }
        }

        $this->info("Отправлено напоминаний: {$sent}.");

        return self::SUCCESS;
    }

    private function readingExists(Rooms $room, CarbonImmutable $periodStart): bool
    {
        return $room->utilityReadings()
            ->whereDate('period_start', $periodStart)
            ->whereIn('status', [
                UtilityReadingStatus::Review->value,
                UtilityReadingStatus::Approved->value,
            ])
            ->exists();
    }

    private function alreadyNotified(Rooms $room, CarbonImmutable $periodStart): bool
    {
        return $room->user
            ->notifications()
            ->where('type', UtilityReadingDueSoonNotification::class)
            ->where('data->rooms_id', $room->id)
            ->where('data->period_start', $periodStart->format('Y-m-d'))
            ->exists();
    }
}
