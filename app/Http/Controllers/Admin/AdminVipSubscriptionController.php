<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipSubscription;
use App\Models\VipPlan;
use Illuminate\Http\Request;

class AdminVipSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = \App\Models\User::with(['vipSubscriptions' => function($q) {
            $q->with('vipPlan');
        }])->latest();

        match($filter) {
            'subscribed' => $query->whereHas('vipSubscriptions', function($q) {
                $q->where('status', 'active')->where('expires_at', '>', now())->where('is_disabled', false);
            }),
            'pending' => $query->whereHas('vipSubscriptions', function($q) {
                $q->where('status', 'pending');
            }),
            'disabled'   => $query->whereHas('vipSubscriptions', function($q) {
                $q->where('is_disabled', true);
            }),
            'flagged'    => $query->whereHas('vipSubscriptions', function($q) {
                $q->where('is_flagged', true);
            }),
            default      => null
        };

        $users = $query->paginate(20)->appends(['filter' => $filter]);

        return view('admin.vip-subscriptions.index', compact('users', 'filter'));
    }

    public function create(Request $request)
    {
        $plans = VipPlan::where('is_active', true)->get();
        $users = \App\Models\User::orderBy('name')->get();
        $targetUser = null;
        if ($request->has('user_id')) {
            $targetUser = \App\Models\User::find($request->user_id);
        }
        return view('admin.vip-subscriptions.create', compact('plans', 'users', 'targetUser'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'vip_plan_id'  => 'required|exists:vip_plans,id',
            'phone'        => 'nullable|string|max:50',
            'starts_at'    => 'required|date',
            'status'       => 'required|in:active,expired,cancelled',
            'is_disabled'  => 'nullable|boolean',
            'is_flagged'   => 'nullable|boolean',
        ]);

        $user = \App\Models\User::findOrFail($validated['user_id']);
        $validated['name'] = $user->name;
        $validated['email'] = $user->email;

        $plan = VipPlan::findOrFail($validated['vip_plan_id']);
        $startsAt  = \Carbon\Carbon::parse($validated['starts_at']);
        $expiresAt = $startsAt->copy()->addDays((int) $plan->duration_days);

        $validated['expires_at']         = $expiresAt;
        $validated['payment_reference']  = 'Manual-Admin-' . uniqid();
        $validated['is_disabled']        = $request->boolean('is_disabled');
        $validated['is_flagged']         = $request->boolean('is_flagged');

        VipSubscription::create($validated);

        return redirect()->route('admin.vip-subscriptions.index')
            ->with('success', 'VIP Member registered successfully.');
    }

    public function edit(VipSubscription $vipSubscription)
    {
        $plans = VipPlan::where('is_active', true)->get();
        return view('admin.vip-subscriptions.edit', compact('vipSubscription', 'plans'));
    }

    public function update(Request $request, VipSubscription $vipSubscription)
    {
        $validated = $request->validate([
            'vip_plan_id'        => 'required|exists:vip_plans,id',
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'starts_at'          => 'required|date',
            'status'             => 'required|in:active,expired,cancelled',
            'recalculate_expiry' => 'nullable|boolean',
            'is_disabled'        => 'nullable|boolean',
            'is_flagged'         => 'nullable|boolean',
        ]);

        if ($request->has('recalculate_expiry') && $request->recalculate_expiry) {
            $plan = VipPlan::findOrFail($validated['vip_plan_id']);
            $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
            $validated['expires_at'] = $startsAt->copy()->addDays((int) $plan->duration_days);
        }

        unset($validated['recalculate_expiry']);

        // Handle checkboxes explicitly (unchecked = not sent = false)
        $validated['is_disabled'] = $request->boolean('is_disabled');
        $validated['is_flagged']  = $request->boolean('is_flagged');

        $vipSubscription->update($validated);

        return redirect()->route('admin.vip-subscriptions.index')
            ->with('success', 'VIP Member updated successfully.');
    }

    public function destroy(VipSubscription $vipSubscription)
    {
        $vipSubscription->delete();
        return redirect()->route('admin.vip-subscriptions.index')
            ->with('success', 'VIP Member deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:vip_subscriptions,id'
        ]);

        $count = VipSubscription::whereIn('id', $validated['selected_ids'])->delete();

        return redirect()->route('admin.vip-subscriptions.index')
            ->with('success', $count . ' VIP Members deleted successfully.');
    }
}
