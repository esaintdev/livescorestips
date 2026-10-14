<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Models\VipPlan;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class VipPlanController extends Controller
{
    public function index(CurrencyService $currencyService): JsonResponse
    {
        $plans = VipPlan::active()
            ->ordered()
            ->whereNull('country_name')
            ->get()
            ->groupBy('category')
            ->map(fn ($package) => $package->map(fn ($plan) => $this->toPlan($plan)));

        $metadata = Metadata::where('route_name', 'vip.index')->pluck('value', 'key');

        return response()->json([
            'data' => $plans,
            'countries' => $currencyService->all(),
            'meta' => [
                'page_title' => $metadata['page_title'] ?? null,
                'page_description' => $metadata['page_description'] ?? null,
                'page_subheader' => $metadata['page_subheader'] ?? null,
            ],
        ]);
    }

    private function toPlan(VipPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'category' => $plan->category,
            'name' => $plan->name,
            'duration' => $plan->duration,
            'duration_days' => $plan->duration_days,
            'price' => (float) $plan->price,
            'currency' => $plan->currency,
            'badge_text' => $plan->badge_text,
            'features' => $plan->features ?? [],
            'sort_order' => $plan->sort_order,
        ];
    }
}
