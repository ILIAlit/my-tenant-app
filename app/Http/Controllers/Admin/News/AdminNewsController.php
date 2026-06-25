<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\NewsCreateRequest;
use App\Http\Requests\News\NewsDeleteRequest;
use App\Http\Requests\News\NewsUpdateRequest;
use App\Models\News;
use App\Models\User;
use App\Notifications\NewsPublishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class AdminNewsController extends Controller
{
    public function createNews(NewsCreateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $news = new News($request->validated());
        $news->date = Carbon::parse($news->date)->format('Y-m-d');

        $user->news()->save($news);

        $recipients = User::query()->whereKeyNot($user->id)->get();
        Notification::send($recipients, new NewsPublishedNotification($news));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Объявление создано.')]);

        return to_route('news.get');
    }

    public function deleteNews(NewsDeleteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        News::destroy($validated['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Объявление удалено.')]);

        return to_route('news.get');
    }

    public function updateNews(NewsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $news = News::findOrFail($validated['id']);
        $news->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Объявление обновлено.')]);

        return to_route('news.get');
    }
}
