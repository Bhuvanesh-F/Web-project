<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * HTTP Kernel — VitalCare
 *
 * Registers all middleware stacks:
 *   - $middleware          → run on every request
 *   - $middlewareGroups    → web stack (sessions, CSRF) and api stack
 *   - $routeMiddleware     → named middleware for use in routes/controllers
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware.
     * Runs on every request to the application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // Security: trust proxies if behind load balancer/Nginx
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // CSRF protection on all web routes — Ayman (Security)
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // XSS sanitization on all web POST/PUT/PATCH — Ayman (Security)
            \App\Http\Middleware\SanitizeInput::class,
        ],

        'api' => [
            // Sanctum stateful API support (reads session cookie for SPA)
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // XSS sanitization on API inputs — Ayman (Security)
            \App\Http\Middleware\SanitizeInput::class,
        ],
    ];

    /**
     * Named route middleware.
     * These can be applied individually to routes or controllers.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive'     => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed'           => \App\Http\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ── VitalCare custom middleware (Ayman) ──────────────────────────────
        // Role-based access control
        'role'             => \App\Http\Middleware\RoleCheck::class,
        // Session hijacking prevention
        'secure.session'   => \App\Http\Middleware\SecureSession::class,
        // XSS input sanitization (also in groups, aliased here for explicit use)
        'sanitize'         => \App\Http\Middleware\SanitizeInput::class,
    ];
}
