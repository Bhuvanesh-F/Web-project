<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RedirectIfAuthenticated (guest middleware)
 * Redirects already-logged-in users away from /login and /register
 * to their role-appropriate dashboard.
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                $route = match ($user->role) {
                    'admin'        => route('admin.dashboard'),
                    'doctor'       => route('doctor.dashboard'),
                    'vet'          => route('vet.dashboard'),
                    'nurse'        => route('nurse.dashboard'),
                    'receptionist' => route('receptionist.dashboard'),
                    'pet_owner'    => route('pet-owner.dashboard'),
                    default        => route('patient.dashboard'),
                };

                return redirect($route);
            }
        }

        return $next($request);
    }
}
