<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 *
 * Handles all authentication actions for the VitalCare API:
 * login, register, logout, and profile retrieval.
 * Uses Laravel Sanctum for API token-based authentication.
 *
 * Security measures implemented:
 * - Bcrypt password hashing (via Hash::make)
 * - Sanctum token issuance and revocation
 * - Role-based token abilities
 * - Audit logging on login/logout
 * - Rate limiting via route middleware (throttle:5,1)
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Authenticate a user and return a Sanctum token.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            // Log failed login attempt (security audit trail)
            Log::warning('Failed login attempt', [
                'email'      => $request->input('email'),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials. Please check your email and password.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Revoke all previous tokens for this user (single active session)
        $user->tokens()->delete();

        // Issue a new token with role-based abilities
        $token = $user->createToken(
            name: 'vitalcare-api-token',
            abilities: $this->getRoleAbilities($user->role)
        )->plainTextToken;

        // Log successful login
        Log::info('User logged in', [
            'user_id' => $user->id,
            'role'    => $user->role,
            'ip'      => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $this->formatUserResponse($user),
            ],
        ], 200);
    }

    /**
     * POST /api/auth/register
     * Register a new patient or pet owner account.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'     => $request->input('role', 'patient'),
            'phone'    => $request->input('phone'),
        ]);

        // Issue token immediately upon registration
        $token = $user->createToken(
            name: 'vitalcare-api-token',
            abilities: $this->getRoleAbilities($user->role)
        )->plainTextToken;

        Log::info('New user registered', [
            'user_id' => $user->id,
            'role'    => $user->role,
            'ip'      => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Welcome to VitalCare!',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $this->formatUserResponse($user),
            ],
        ], 201);
    }

    /**
     * POST /api/auth/logout
     * Revoke the current user's API token.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Delete current token only (targeted revocation)
        $request->user()->currentAccessToken()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }

    /**
     * GET /api/auth/me
     * Return the authenticated user's profile.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => $this->formatUserResponse($user),
        ], 200);
    }

    /**
     * Return role-specific token abilities.
     * These abilities are checked by middleware on protected routes.
     *
     * @param  string  $role
     * @return array<string>
     */
    private function getRoleAbilities(string $role): array
    {
        return match ($role) {
            'admin'        => ['admin:*', 'doctor:read', 'patient:read', 'appointment:*'],
            'doctor'       => ['doctor:*', 'patient:read', 'appointment:read', 'record:*'],
            'vet'          => ['vet:*', 'pet:read', 'pet-appointment:read', 'pet-record:*'],
            'nurse'        => ['nurse:*', 'patient:read', 'appointment:read'],
            'receptionist' => ['receptionist:*', 'appointment:*', 'patient:read'],
            'pet_owner'    => ['pet-owner:*', 'pet:*', 'pet-appointment:*'],
            default        => ['patient:read', 'appointment:read', 'record:read'],
        };
    }

    /**
     * Format user data for API response.
     * Never expose password hash in responses.
     *
     * @param  User  $user
     * @return array<string, mixed>
     */
    private function formatUserResponse(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'phone'      => $user->phone,
            'created_at' => $user->created_at?->toISOString(),
            'dashboard'  => $this->getRoleDashboardUrl($user->role),
        ];
    }

    /**
     * Return the dashboard redirect URL for each role.
     *
     * @param  string  $role
     * @return string
     */
    private function getRoleDashboardUrl(string $role): string
    {
        return match ($role) {
            'admin'        => '/admin/dashboard',
            'doctor'       => '/doctor/dashboard',
            'vet'          => '/vet/dashboard',
            'nurse'        => '/nurse/dashboard',
            'receptionist' => '/receptionist/dashboard',
            'pet_owner'    => '/pet-owner/dashboard',
            default        => '/patient/dashboard',
        };
    }
}
