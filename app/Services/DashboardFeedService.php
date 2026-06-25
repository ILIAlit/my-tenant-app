<?php

namespace App\Services;

use App\Models\News;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class DashboardFeedService
{
    private const int NOTIFICATIONS_LIMIT = 10;

    private const int NEWS_LIMIT = 6;

    /**
     * @return array{
     *     unread_count: int,
     *     items: list<array{
     *         id: string,
     *         type: string,
     *         title: string,
     *         message: string,
     *         url: string|null,
     *         created_at: string,
     *         read_at: string|null,
     *     }>,
     * }
     */
    public function notifications(User $user): array
    {
        return [
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()
                ->latest()
                ->limit(self::NOTIFICATIONS_LIMIT)
                ->get()
                ->map(fn (DatabaseNotification $notification): array => [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'info',
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'created_at' => $notification->created_at->format('Y-m-d H:i'),
                    'read_at' => $notification->read_at?->format('Y-m-d H:i'),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     title: string,
     *     text: string,
     *     date: string,
     * }>
     */
    public function news(): array
    {
        return News::query()
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(self::NEWS_LIMIT)
            ->get()
            ->map(fn (News $news): array => [
                'id' => $news->id,
                'title' => $news->title,
                'text' => $news->text,
                'date' => $news->date->format('Y-m-d'),
            ])
            ->all();
    }
}
