<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipPlan;
use Illuminate\Http\Request;

class AdminVipPlanController extends Controller
{
    public function index()
    {
        $plans = VipPlan::withCount(['subscriptions' => function ($query) {
            $query->active();
        }])
        ->ordered()
        ->paginate(10);

        return view('admin.vip-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.vip-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', VipPlan::CATEGORIES),
            'name' => 'required|string|max:255',
            'duration' => 'required|string|max:100',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:5',
            'country_name' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:5',
            'badge_text' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Parse features from comma-separated string to array
        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode(',', $validated['features']));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['currency'] = $validated['currency'] ?? '$';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        VipPlan::create($validated);

        return redirect()->route('admin.vip-plans.index')
            ->with('success', 'VIP Plan created successfully.');
    }

    public function edit(VipPlan $vipPlan)
    {
        return view('admin.vip-plans.edit', compact('vipPlan'));
    }

    public function update(Request $request, VipPlan $vipPlan)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', VipPlan::CATEGORIES),
            'name' => 'required|string|max:255',
            'duration' => 'required|string|max:100',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:5',
            'country_name' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:5',
            'badge_text' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Parse features from comma-separated string to array
        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode(',', $validated['features']));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['currency'] = $validated['currency'] ?? '$';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $vipPlan->update($validated);

        return redirect()->route('admin.vip-plans.index')
            ->with('success', 'VIP Plan updated successfully.');
    }

    public function destroy(VipPlan $vipPlan)
    {
        $vipPlan->delete();

        return redirect()->route('admin.vip-plans.index')
            ->with('success', 'VIP Plan deleted successfully.');
    }
}
