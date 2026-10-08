<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * Store and publish a new article (e.g. from ChatGPT Custom GPT Action).
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Verify API Key
        $apiKey = $request->header('X-API-Key') ?? $request->bearerToken() ?? $request->input('api_key');
        $configuredKey = config('services.article_api.key', env('ARTICLE_API_KEY'));

        if (!$configuredKey || !hash_equals((string) $configuredKey, (string) $apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API key.',
            ], 401);
        }

        // 2. Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:2000',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'publish' => 'nullable|boolean',
        ]);

        // 3. Resolve or create category
        $categoryId = null;
        $categoryName = $request->input('category');
        if ($categoryName) {
            $cat = ArticleCategory::where('name', 'LIKE', $categoryName)
                ->orWhere('slug', Str::slug($categoryName))
                ->first();
            if (!$cat) {
                $cat = ArticleCategory::create([
                    'name' => ucwords($categoryName),
                    'slug' => Str::slug($categoryName),
                    'description' => "Guides and articles about {$categoryName}",
                ]);
            }
            $categoryId = $cat->id;
        } else {
            $defaultCat = ArticleCategory::where('slug', 'guides')
                ->orWhere('slug', 'tips-strategies')
                ->orWhere('slug', 'news')
                ->first();
            $categoryId = $defaultCat?->id;
        }

        // 4. Generate unique SEO slug
        $slug = Str::slug($request->input('title'));
        $originalSlug = $slug;
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        // 5. Download and store image if provided
        $imagePath = null;
        if ($request->filled('image_url')) {
            try {
                $imageUrl = $request->input('image_url');
                $response = Http::timeout(30)->get($imageUrl);
                if ($response->successful()) {
                    $imageContent = $response->body();
                    $filename = "{$slug}.jpg";

                    // Save to storage/app/public/articles
                    Storage::disk('public')->put("articles/{$filename}", $imageContent);

                    // Also save directly to public/articles for standard web serving
                    $publicDir = public_path('articles');
                    if (!File::exists($publicDir)) {
                        File::makeDirectory($publicDir, 0755, true);
                    }
                    File::put("{$publicDir}/{$filename}", $imageContent);

                    $imagePath = "articles/{$filename}";
                } else {
                    Log::warning("ArticleController::store: Failed to download image from {$imageUrl}, HTTP status: " . $response->status());
                }
            } catch (\Throwable $e) {
                Log::warning("ArticleController::store: Exception downloading image: " . $e->getMessage());
            }
        }

        // 6. Meta description & keywords fallbacks
        $metaDescription = $request->input('meta_description')
            ?: Str::limit(strip_tags($request->input('content')), 160);

        $metaKeywords = $request->input('meta_keywords')
            ?: ($categoryName ? strtolower($categoryName) . ', ' : '') . 'football betting guide, predictions, betting tips, ' . strtolower($request->title);

        $isPublish = $request->input('publish', true);

        // 7. Create Article
        $article = Article::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image' => $imagePath,
            'category_id' => $categoryId,
            'meta_description' => $metaDescription,
            'meta_keywords' => Str::limit($metaKeywords, 255, ''),
            'published_at' => $isPublish ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article published successfully!',
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'url' => route('blog.show', $article->slug),
                'published_at' => $article->published_at?->format('Y-m-d H:i:s'),
                'category' => $article->category?->name,
                'image' => $article->image ? asset($article->image) : null,
            ],
        ], 201);
    }
}