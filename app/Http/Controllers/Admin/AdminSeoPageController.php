<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use Illuminate\Http\Request;

class AdminSeoPageController extends Controller
{
    public function index()
    {
        $seoPages = SeoPage::latest()->paginate(10);
        return view('admin.seo-pages.index', compact('seoPages'));
    }

    public function create()
    {
        return view('admin.seo-pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_header' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:seo_pages,slug',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        SeoPage::create($validated);

        return redirect()->route('admin.seo-pages.index')->with('success', 'SEO Page created successfully.');
    }

    public function edit(SeoPage $seoPage)
    {
        return view('admin.seo-pages.edit', compact('seoPage'));
    }

    public function update(Request $request, SeoPage $seoPage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_header' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:seo_pages,slug,' . $seoPage->id,
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $seoPage->update($validated);

        return redirect()->route('admin.seo-pages.index')->with('success', 'SEO Page updated successfully.');
    }

    public function destroy(SeoPage $seoPage)
    {
        $seoPage->delete();
        return redirect()->route('admin.seo-pages.index')->with('success', 'SEO Page deleted successfully.');
    }
}
