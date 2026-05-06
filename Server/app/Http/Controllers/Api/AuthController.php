<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Token-based auth via Sanctum personal access tokens.
 *
 * We use the *token* flavour (not the SPA-cookie flavour) because the
 * front-end runs on a different origin during dev (5283 vs 8765) and may
 * land on yet another origin in production. Tokens travel in the
 * Authorization header and are easy to revoke per device.
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        // Issue a token immediately so the SPA can drop the user straight
        // onto the dashboard rather than bouncing them through a login form.
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            // Generic message — do not reveal whether the email exists.
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke just the current token, not every device the user has
        // signed in on.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
