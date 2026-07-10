<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Script;
use Illuminate\Http\Request;

class AdminScriptController extends Controller
{
    public function index()
    {
        $scripts = Script::latest()->paginate(10);
        return view('admin.scripts.index', compact('scripts'));
    }

    public function create()
    {
        return view('admin.scripts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'position' => 'required|in:head,body,banner',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Script::create($validated);

        return redirect()->route('admin.scripts.index')
            ->with('success', 'Script added successfully.');
    }

    public function edit(Script $script)
    {
        return view('admin.scripts.edit', compact('script'));
    }

    public function update(Request $request, Script $script)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'position' => 'required|in:head,body,banner',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $script->update($validated);

        return redirect()->route('admin.scripts.index')
            ->with('success', 'Script updated successfully.');
    }

    public function destroy(Script $script)
    {
        $script->delete();

        return redirect()->route('admin.scripts.index')
            ->with('success', 'Script deleted successfully.');
    }
}
