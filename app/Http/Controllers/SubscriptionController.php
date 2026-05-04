<?php

namespace App\Http\Controllers;

use App\Enums\PriceIdsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->orWhere('stripe_status', 'trialing')
            ->first();

        return response()->json([
            'has_subscription' => $user->hasProPlan(),
            'subscription' => $subscription,
            'plan' => $user->plan()
        ]);
    }

    public function checkout(Request $request)
    {
        $plan = PriceIdsEnum::from($request->plan);
        
        $checkout = $request->user()
            ->newSubscription('pro', $plan->priceId())
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => config('app.frontend_url'),
                'cancel_url' => config('app.frontend_url'),
            ]);

        return response()->json(['url' => $checkout->url]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->orWhere('stripe_status', 'trialing')
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found'], 404);
        }

        $subscription->cancel();

        return response()->json(['message' => 'Subscription cancelled successfully']);
    }
}
