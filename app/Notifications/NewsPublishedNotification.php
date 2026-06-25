<?php

namespace App\Notifications;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewsPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public News $news) {}

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
            'type' => 'news_published',
            'title' => __('Новое объявление'),
            'message' => $this->news->title,
            'url' => '/news',
        ];
    }
}
