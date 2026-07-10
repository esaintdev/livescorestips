<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use App\Models\Prediction;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a comment for an article.
     */
    public function store(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        Comment::create([
            'commentable_type' => Article::class,
            'commentable_id' => $article->id,
            'article_id' => $article->id, // Keep for backwards compatibility
            'name' => $request->name,
            'email' => $request->email,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve for now
        ]);

        return back()->with('success', 'Your comment has been posted successfully!');
    }

    /**
     * Store a comment for a prediction.
     */
    public function storeForPrediction(Request $request, $slugOrId)
    {
        $prediction = Prediction::where('slug', $slugOrId)
            ->orWhere('fixture_id', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        Comment::create([
            'commentable_type' => Prediction::class,
            'commentable_id' => $prediction->id,
            'name' => $request->name,
            'email' => $request->email,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve for now
        ]);

        return back()->with('success', 'Your comment has been posted successfully!');
    }
}
