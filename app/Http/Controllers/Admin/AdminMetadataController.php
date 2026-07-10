<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use Illuminate\Http\Request;

class AdminMetadataController extends Controller
{

    public function index(Request $request)
    {
        $pages = [
            'General Pages' => [
                'home' => 'Home Page',
                'predictions.all' => 'All Predictions Page',
                'banker-predictions' => 'Banker Predictions Page',
                'blog.index' => 'Blog Page',
                'contact' => 'Contact Page',
                'about' => 'About Page',
                'vip.index' => 'VIP Page',
                'recent-winnings.index' => 'Recent Winnings Page',
                'worldcup.index' => 'World Cup 2026 Page',
            ],
            'Prediction Categories' => [
                'category:double-chance' => 'Double Chance',
                'category:over-1-5-goals' => 'Over 1.5 Goals',
                'category:over-2-5-goals' => 'Over 2.5 Goals',
                'category:btts-gg' => 'BTTS/GG',
                'category:draws' => 'Draws',
                'category:home-wins' => 'Home Wins',
                'category:away-wins' => 'Away Wins',
                'category:correct-score' => 'Correct Score',
            ],
        ];

        $currentRoute = $request->query('page', 'home');

        $validRoutes = [];
        foreach ($pages as $group) {
            $validRoutes = array_merge($validRoutes, array_keys($group));
        }

        if (!in_array($currentRoute, $validRoutes)) {
            $currentRoute = 'home';
        }

        $metadata = Metadata::where('route_name', $currentRoute)->get()->pluck('value', 'key');

        return view('admin.metadata.index', compact('pages', 'currentRoute', 'metadata'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string',
            'page_title' => 'nullable|string|max:255',
            'page_subheader' => 'nullable|string',
            'page_description' => 'nullable|string',
            'header_intro' => 'nullable|string',
            'page_keywords' => 'nullable|string',
            'header_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
        ]);

        $routeName = $request->route_name;
        $fields = ['page_title', 'page_subheader', 'page_description', 'header_intro', 'page_keywords', 'header_content', 'footer_content'];

        foreach ($fields as $key) {
            Metadata::updateOrCreate(
                ['route_name' => $routeName, 'key' => $key],
                ['value' => $request->input($key)]
            );
        }

        return redirect()->route('admin.metadata.index', ['page' => $routeName])
            ->with('success', 'SEO Metadata updated successfully for ' . $routeName);
    }

    public function flush()
    {
        Metadata::truncate();
        return redirect()->route('admin.metadata.index')
            ->with('success', 'All SEO metadata has been flushed.');
    }
}
