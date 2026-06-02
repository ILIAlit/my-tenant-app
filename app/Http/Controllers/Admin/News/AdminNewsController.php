<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\NewsCreateRequest;
use App\Http\Requests\News\NewsDeleteRequest;
use App\Http\Requests\News\NewsUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\News;
use Illuminate\Support\Carbon;

class AdminNewsController extends Controller
{
    public function createNews(NewsCreateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $news = new News($request->validated());
        $news->date = Carbon::parse($news->date)->format('Y-m-d');

        $user->news()->save($news);

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
