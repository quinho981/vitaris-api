<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubscriptionSyncService
{
    /**
     * Synchronization of user subscription data
     */
    public static function syncUserData($userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) return false;

            $plan = $user->plan();
            $hasSubscription = $user->hasProPlan();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
