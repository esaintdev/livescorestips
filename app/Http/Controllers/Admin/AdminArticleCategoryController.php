<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminArticleCategoryController extends Controller
{
    public function index()
    {
        $categories = ArticleCategory::withCount('articles')->latest()->paginate(15);
        return view('admin.article-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.article-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:article_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($request->name);
        $count = ArticleCategory::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        ArticleCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ArticleCategory $article_category)
    {
        return view('admin.article-categories.edit', ['category' => $article_category]);
    }

    public function update(Request $request, ArticleCategory $article_category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:article_categories,name,' . $article_category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $slug = $article_category->slug;
        if ($request->name !== $article_category->name) {
            $slug = Str::slug($request->name);
            $count = ArticleCategory::where('slug', 'LIKE', "{$slug}%")
                ->where('id', '!=', $article_category->id)
                ->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
        }

        $article_category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ArticleCategory $article_category)
    {
        // Articles in this category will have their category_id set to NULL 
        // because of the onDelete('set null') constraint in the migration.
        $article_category->delete();

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
