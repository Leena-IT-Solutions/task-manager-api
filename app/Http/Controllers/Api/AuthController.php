<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate via email or mobile number and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'], // can be email or mobile
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $loginInput = trim($request->input('login'));
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $user = $isEmail
            ? User::where('email', $loginInput)->first()
            : User::where('mobile', preg_replace('/[^0-9]/', '', $loginInput))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('The provided credentials do not match our records.')],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is deactivated. Please contact administrator.',
            ], 403);
        }

        $deviceName = $request->input('device_name', 'mobile_app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->role,
                'designation' => $user->designation,
                'department' => $user->department,
                'reporting_to_id' => $user->reporting_to_id,
                'manager' => $user->manager ? [
                    'id' => $user->manager->id,
                    'name' => $user->manager->name,
                    'email' => $user->manager->email,
                    'designation' => $user->manager->designation,
                ] : null,
            ],
        ]);
    }

    /**
     * Get authenticated user profile with reporting hierarchy.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['manager', 'subordinates']);

        return response()->json([
            'status' => true,
            'user' => $user,
            'subordinates_count' => $user->subordinates->count(),
        ]);
    }

    /**
     * Revoke current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
        ]);
    }
}
