<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->hasProPlan()) {
            return response()->json([
                'message' => 'This feature requires a Pro subscription',
                'requires_pro' => true
            ], 403);
        }

        return $next($request);
    }
}
