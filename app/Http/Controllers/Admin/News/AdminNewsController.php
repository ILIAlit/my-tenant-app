<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\NewsCreateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\News;

class AdminNewsController extends Controller
{
    public function createNews(NewsCreateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $news = new News($request->validated());
        $user->news()->save($news);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Объявление создано.')]);

        return to_route('news.get');
    }
}
