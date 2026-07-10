<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest()->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = \App\Models\ArticleCategory::all();
        return view('admin.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|file|max:10240',
            'category_id' => 'nullable|exists:article_categories,id',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->title);
        $count = Article::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
        }

        Article::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image' => $imagePath,
            'category_id' => $request->input('category_id'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'published_at' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = \App\Models\ArticleCategory::all();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|file|max:10240',
            'category_id' => 'nullable|exists:article_categories,id',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->title);
        if ($request->title !== $article->title) {
            $count = Article::where('slug', 'LIKE', "{$slug}%")->where('id', '!=', $article->id)->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
        } else {
            $slug = $article->slug;
        }

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $article->image = $request->file('image')->store('articles', 'public');
        }

        $publish = $request->boolean('publish');
        $article->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image' => $article->image,
            'category_id' => $request->input('category_id'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'published_at' => $publish ? now() : null,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully.');
    }
}
