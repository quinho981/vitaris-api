<?php

namespace App\Http\Controllers;

use App\Enums\PriceIdsEnum;
use App\Http\Controllers\Controller;
use App\Services\SubscriptionSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                'success_url' => config('app.frontend_url') . '/?checkout_success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/?checkout_cancelled=true',
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

    public function verifyCheckout(Request $request)
    {
        $sessionId = $request->session_id;
        
        if (!$sessionId) return response()->json(['error' => 'Session ID not provided'], 400);

        try {
            $session = $request->user()->stripe()->checkout->sessions->retrieve($sessionId);
            
            if ($session->payment_status === 'paid' && $session->status === 'complete') {
                $user = $request->user();
                SubscriptionSyncService::syncUserData($user->id);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'subscription' => $user->subscriptions()->latest()->first(),
                    'plan' => $user->plan()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment not completed',
                'status' => $session->payment_status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to verify checkout'], 500);
        }
    }
}
