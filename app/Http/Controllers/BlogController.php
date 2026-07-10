<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('category')
            ->latest('published_at')
            ->paginate(10);

        $categories = \App\Models\ArticleCategory::all();
        $metadata = \App\Models\Metadata::where('route_name', 'blog.index')->pluck('value', 'key');

        return view('blog.index', compact('posts', 'metadata', 'categories'));
    }

    public function resolve($slug)
    {
        $category = \App\Models\ArticleCategory::where('slug', $slug)->first();
        if ($category) {
            return $this->category($slug);
        }
        return $this->show($slug);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['comments', 'category'])
            ->firstOrFail();

        // Get latest articles for sidebar
        $latestArticles = Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        $categories = \App\Models\ArticleCategory::all();

        return view('blog.show', [
            'post' => $article,
            'latestArticles' => $latestArticles,
            'categories' => $categories
        ]);
    }

    public function category($slug)
    {
        $category = \App\Models\ArticleCategory::where('slug', $slug)->firstOrFail();
        $categories = \App\Models\ArticleCategory::all();

        $posts = \App\Models\Article::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('category_id', $category->id)
            ->with('category')
            ->latest('published_at')
            ->paginate(10);

        $metadata = \App\Models\Metadata::where('route_name', 'blog.index')->pluck('value', 'key');

        return view('blog.index', compact('posts', 'category', 'categories', 'metadata'));
    }
}
