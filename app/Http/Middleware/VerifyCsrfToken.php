<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * VerifyCsrfToken
 *
 * CSRF protection is applied to all web routes automatically.
 * API routes are excluded because they use Sanctum token auth instead.
 *
 * Security note: DO NOT add web routes to $except — that would create
 * CSRF vulnerabilities. Only stateless API endpoints are excluded.
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * URIs that should be excluded from CSRF verification.
     * Only Sanctum-protected API routes are safe to exclude.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
    ];
}
