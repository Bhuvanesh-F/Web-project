<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * WebAuthController
 *
 * Manages web-based (session) authentication flows:
 *   - Show login form
 *   - Handle login POST (with CSRF, brute-force throttle, session regeneration)
 *   - Handle logout (session invalidation)
 *   - Multi-step patient registration (steps 1, 2, 3)
 *
 * Security implemented:
 *   - Session::regenerate() after login to prevent session fixation
 *   - Auth::logout() + Session::invalidate() on logout to destroy all session data
 *   - Session::regenerateToken() refreshes CSRF token after auth state change
 *   - Role-based redirect after login
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class WebAuthController extends Controller
{
    /**
     * GET /login
     * Show the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Redirect already-authenticated users to their dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * POST /login
     * Authenticate the user and redirect to role-appropriate dashboard.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // SECURITY: Regenerate session ID to prevent session fixation
            $request->session()->regenerate();

            // Refresh CSRF token for the new session
            $request->session()->regenerateToken();

            $user = Auth::user();

            Log::info('Web login successful', [
                'user_id' => $user->id,
                'role'    => $user->role,
                'ip'      => $request->ip(),
            ]);

            return $this->redirectToDashboard($user->role)
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Log failed attempts
        Log::warning('Web login failed', [
            'email' => $request->input('email'),
            'ip'    => $request->ip(),
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * POST /logout
     * Destroy the user session completely.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();

        // SECURITY: Fully invalidate the session (clears all session data)
        $request->session()->invalidate();

        // SECURITY: Regenerate CSRF token so old tokens cannot be reused
        $request->session()->regenerateToken();

        Log::info('Web logout', ['user_id' => $userId, 'ip' => $request->ip()]);

        return redirect()->route('auth.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * GET /register
     * Show patient registration step 1 (personal info).
     */
    public function showRegisterStep1(): View
    {
        return view('auth.register-step1');
    }

    /**
     * GET /register/step-2
     * Show patient registration step 2 (medical info / pet info).
     */
    public function showRegisterStep2(): View
    {
        return view('auth.register-step2');
    }

    /**
     * GET /register/step-3
     * Show patient registration step 3 (account credentials).
     */
    public function showRegisterStep3(): View
    {
        return view('auth.register-step3');
    }

    /**
     * POST /register
     * Process the complete registration form.
     * Registration data is collected client-side across 3 steps and
     * submitted in a single POST to this endpoint.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:100'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'confirmed', 'min:8'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'role'                  => ['sometimes', 'in:patient,pet_owner'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'patient',
            'phone'    => $validated['phone'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        Log::info('New user registered via web', [
            'user_id' => $user->id,
            'role'    => $user->role,
            'ip'      => $request->ip(),
        ]);

        return $this->redirectToDashboard($user->role)
            ->with('success', 'Welcome to VitalCare, ' . $user->name . '!');
    }

    /**
     * Build a redirect response to the correct dashboard for the given role.
     *
     * @param  string  $role
     * @return RedirectResponse
     */
    private function redirectToDashboard(string $role): RedirectResponse
    {
        $route = match ($role) {
            'admin'        => 'admin.dashboard',
            'doctor'       => 'doctor.dashboard',
            'vet'          => 'vet.dashboard',
            'nurse'        => 'nurse.dashboard',
            'receptionist' => 'receptionist.dashboard',
            'pet_owner'    => 'pet-owner.dashboard',
            default        => 'patient.dashboard',
        };

        return redirect()->route($route);
    }
}
