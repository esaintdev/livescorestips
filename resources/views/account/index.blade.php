@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'My Account - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Monitor your VIP subscriptions and account details.')

@section('content')
<div class="container py-5">
    
    {{-- One-Click Renewal Banner for Free/Expired Users --}}
    @if(!$activeSubscription)
        @php
            // Find the last plan they tried to buy, or default to general VIP index
            $lastSub = $subscriptions->first();
            $renewRoute = $lastSub && $lastSub->vip_plan_id ? route('vip.checkout', $lastSub->vip_plan_id) : route('vip.index');
            $renewText = $lastSub && $lastSub->vipPlan ? 'Renew Your ' . $lastSub->vipPlan->name : 'Upgrade to VIP';
        @endphp
        <div class="bm-card mb-5 p-4 p-md-5 text-center" style="background: linear-gradient(135deg, rgba(255,107,0,0.08) 0%, rgba(0,0,0,0) 100%); border: 1px dashed rgba(255,107,0,0.4); border-radius: 16px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; left: -50px; width: 100px; height: 100px; background: rgba(255,107,0,0.2); border-radius: 50%; filter: blur(40px);"></div>
            <div style="position: absolute; bottom: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255,107,0,0.2); border-radius: 50%; filter: blur(40px);"></div>
            
            <h3 class="text-warning font-weight-bold mb-2" style="letter-spacing: 0.5px;"><i class="fa fa-unlock-alt mr-2"></i> Unlock Premium Predictions</h3>
            <p class="text-muted mb-4" style="max-width: 600px; margin: 0 auto; font-size: 1.05rem;">Your account is currently on the free tier. Upgrade to instantly access our highest-accuracy VIP slips and exclusive daily predictions.</p>
            <a href="{{ $renewRoute }}" class="btn btn-warning font-weight-bold py-3 px-5 bm-pulse-button" style="border-radius: 12px; font-size: 1.15rem; background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%); border: none; box-shadow: 0 4px 20px rgba(255,107,0,0.3); color: #000;">
                <i class="fa fa-bolt mr-2"></i> {{ $renewText }} Now
            </a>
        </div>
    @endif
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h2 style="font-weight: 800; color: #fff; font-size: 2.2rem;">My <span class="text-warning">Account</span></h2>
            <p class="text-muted">Welcome back, {{ $user->name }}. Here you can monitor your VIP subscriptions.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger" style="border-radius: 8px;">
                    <i class="fa fa-sign-out mr-1"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Profile Information --}}
        <div class="col-lg-4 mb-4">
            <div class="bm-card p-4" style="background: #1a1a1a; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                <div class="text-center mb-4">
                    <div class="d-inline-flex justify-content-center align-items-center" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,107,0,0.1); color: #ff6b00; font-size: 2rem;">
                        <i class="fa fa-user"></i>
                    </div>
                    <h4 class="text-white mt-3 mb-0">{{ $user->name }}</h4>
                    <p class="text-muted small mb-1">{{ $user->email }}</p>
                    <p class="text-warning small mb-0"><i class="fa fa-phone mr-1"></i> {{ $user->phone ?? 'No phone added' }}</p>
                </div>
                
                <hr style="border-color: rgba(255,255,255,0.05);">
                
                <div class="mb-3">
                    <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Account Status</span>
                    @if($activeSubscription)
                        <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem; border-radius: 6px;"><i class="fa fa-check-circle mr-1"></i> VIP Member</span>
                    @else
                        <span class="badge badge-secondary px-3 py-2" style="font-size: 0.9rem; border-radius: 6px;">Free Member</span>
                    @endif
                </div>
                
                @if($activeSubscription)
                <div class="mb-3">
                    <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Active Plan</span>
                    <span class="text-warning font-weight-bold">{{ $activeSubscription->vipPlan->name ?? 'Custom Plan' }}</span>
                </div>
                <div>
                    <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Expires On</span>
                    <span class="text-white">{{ $activeSubscription->expires_at->format('M d, Y') }} ({{ $activeSubscription->expires_at->diffForHumans() }})</span>
                </div>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('vip.index') }}" class="btn btn-warning btn-block font-weight-bold py-2" style="border-radius: 8px; background: linear-gradient(135deg, #ff6b00 0%, #d25800 100%); border: none; color: #000;">
                        <i class="fa fa-star mr-1"></i> View VIP Plans
                    </a>
                </div>
            </div>
        </div>

        {{-- Subscription History --}}
        <div class="col-lg-8">
            <div class="bm-card p-4" style="background: #1a1a1a; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                <h4 class="text-white mb-4"><i class="fa fa-history text-warning mr-2"></i> Subscription History</h4>
                
                @if($subscriptions->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa fa-folder-open-o fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No subscriptions found</h5>
                        <p class="text-muted small">You haven't subscribed to any VIP plans yet.</p>
                        <a href="{{ route('vip.index') }}" class="btn btn-outline-warning mt-2">Get Started</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover" style="color: #eee;">
                            <thead style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="text-muted small text-uppercase">Plan</th>
                                    <th class="text-muted small text-uppercase">Status</th>
                                    <th class="text-muted small text-uppercase">Start Date</th>
                                    <th class="text-muted small text-uppercase">End Date</th>
                                    <th class="text-muted small text-uppercase text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriptions as $sub)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td class="font-weight-bold">{{ $sub->vipPlan->name ?? 'Unknown Plan' }}</td>
                                        <td>
                                            @if($sub->status === 'active' && $sub->expires_at && $sub->expires_at->isFuture())
                                                <span class="badge badge-success">Active</span>
                                            @elseif($sub->status === 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @else
                                                <span class="badge badge-secondary">Expired</span>
                                            @endif
                                        </td>
                                        <td>{{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : '-' }}</td>
                                        <td>{{ $sub->expires_at ? $sub->expires_at->format('M d, Y') : '-' }}</td>
                                        <td class="text-right">
                                            @if($sub->status === 'pending')
                                                @php
                                                    $message = "Hello, I want to send payment proof for my VIP Subscription.\n";
                                                    $message .= "Reference: {$sub->payment_reference}\n";
                                                    $message .= "Plan: " . ($sub->vipPlan->name ?? 'Unknown') . "\n";
                                                    $message .= "Email: {$user->email}";
                                                    $whatsappUrl = 'https://wa.me/2349072020245?text=' . urlencode($message);
                                                @endphp
                                                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-sm btn-outline-success font-weight-bold" style="border-radius: 8px;">
                                                    <i class="fa fa-whatsapp"></i> Send Proof
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
