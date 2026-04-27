<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show()
    {
        $userId = Auth::id();

        $user = User::select(['id', 'name', 'email', 'phone'])
                    ->with('plans')
                    ->find($userId);
        
        return response()->json($user);
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
        ], 200);
    }
}
