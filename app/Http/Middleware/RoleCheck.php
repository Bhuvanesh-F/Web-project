<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleCheck Middleware
 *
 * Enforces role-based access control for all VitalCare routes.
 * Redirects unauthenticated users to login and authenticated users
 * attempting to access unauthorised role dashboards.
 *
 * Security: Prevents privilege escalation by verifying that the
 * authenticated user's role matches the required role(s).
 */
class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string   ...$roles  One or more allowed roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Ensure user is authenticated
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Check if user's role matches any of the allowed roles
        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                    'your_role'     => $user->role,
                    'required_role' => $roles,
                ], 403);
            }

            // Redirect to the user's own dashboard instead of an error page
            return redirect()
                ->to($this->getDashboardRoute($user->role))
                ->with('error', 'Access denied. You do not have permission to view that page.');
        }

        return $next($request);
    }

    /**
     * Return the appropriate dashboard route for a given role.
     *
     * @param  string  $role
     * @return string
     */
    private function getDashboardRoute(string $role): string
    {
        return match ($role) {
            'admin'        => route('admin.dashboard'),
            'doctor'       => route('doctor.dashboard'),
            'vet'          => route('vet.dashboard'),
            'nurse'        => route('nurse.dashboard'),
            'receptionist' => route('receptionist.dashboard'),
            'pet_owner'    => route('pet-owner.dashboard'),
            default        => route('patient.dashboard'),
        };
    }
}
