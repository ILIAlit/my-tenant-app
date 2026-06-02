<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function getNews(Request $request)
    {
        $news = News::all();

        return Inertia::render('news/news', [
            'news' => $news,
        ]);
    }
}
