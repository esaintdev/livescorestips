<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSiteLinkController extends Controller
{
    public function index()
    {
        $links = SiteLink::orderBy('section')->orderBy('order')->get();
        return view('admin.site-links.index', compact('links'));
    }

    public function create()
    {
        return view('admin.site-links.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'section' => 'required|in:partner,footer',
            'image' => 'nullable|image|max:2048', // Only required/relevant for partners usually
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('site-links', 'public');
            // 'public' disk root is public_path(), so path is simply /site-links/...
            $validated['image'] = '/' . $path;
        }

        $validated['is_active'] = $request->has('is_active');

        SiteLink::create($validated);

        return redirect()->route('admin.site-links.index')
            ->with('success', 'Link created successfully.');
    }

    public function edit(SiteLink $siteLink)
    {
        return view('admin.site-links.edit', compact('siteLink'));
    }

    public function update(Request $request, SiteLink $siteLink)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'section' => 'required|in:partner,footer',
            'image' => 'nullable|image|max:2048',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($siteLink->image) {
                // Image is stored as /site-links/filename.jpg
                // Public disk root is public_path()
                // delete expects path relative to root, i.e. site-links/filename.jpg
                $oldPath = ltrim($siteLink->image, '/');
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('image')->store('site-links', 'public');
            $validated['image'] = '/' . $path;
        }

        $validated['is_active'] = $request->has('is_active');

        $siteLink->update($validated);

        return redirect()->route('admin.site-links.index')
            ->with('success', 'Link updated successfully.');
    }

    public function destroy(SiteLink $siteLink)
    {
        if ($siteLink->image) {
            $oldPath = ltrim($siteLink->image, '/');
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $siteLink->delete();

        return redirect()->route('admin.site-links.index')
            ->with('success', 'Link deleted successfully.');
    }
}
