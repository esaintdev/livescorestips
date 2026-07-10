<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Article::where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $articles->through(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'excerpt' => $a->excerpt,
                'image' => $a->image ? url($a->image) : null,
                'published_at' => $a->published_at->format('Y-m-d H:i:s'),
                'url' => route('blog.show', $a->slug),
            ]),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->where('published_at', '<=', now())
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'image' => $article->image ? url($article->image) : null,
                'meta_description' => $article->meta_description,
                'published_at' => $article->published_at->format('Y-m-d H:i:s'),
                'category' => $article->category?->name,
                'url' => route('blog.show', $article->slug),
            ],
        ]);
    }
}