<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecureSession Middleware
 *
 * Prevents session hijacking by:
 * 1. Regenerating session ID on each authenticated request.
 * 2. Binding session to the user's IP address and User-Agent.
 * 3. Invalidating sessions where IP or User-Agent has changed.
 * 4. Enforcing session expiry for idle users.
 *
 * This is applied to all web routes that require authentication.
 */
class SecureSession
{
    /**
     * Session idle timeout in minutes (matches SESSION_LIFETIME in .env).
     */
    protected int $idleTimeoutMinutes = 30;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $this->validateSessionBinding($request);
            $this->checkIdleTimeout($request);
        }

        return $next($request);
    }

    /**
     * Validate that the session's fingerprint matches the current request.
     * If the IP or User-Agent has changed, the session is invalidated —
     * a strong indicator of session hijacking.
     *
     * @param  Request  $request
     * @return void
     */
    private function validateSessionBinding(Request $request): void
    {
        $sessionFingerprint = Session::get('_fingerprint');
        $currentFingerprint = $this->buildFingerprint($request);

        if ($sessionFingerprint === null) {
            // First authenticated request — bind the session
            Session::put('_fingerprint', $currentFingerprint);
            Session::put('_last_active', now()->timestamp);
            // Regenerate session ID to prevent session fixation
            Session::regenerate();
            return;
        }

        if ($sessionFingerprint !== $currentFingerprint) {
            // Fingerprint mismatch — possible session hijacking
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            abort(401, 'Session invalid. Please log in again.');
        }

        // Update last active time
        Session::put('_last_active', now()->timestamp);
    }

    /**
     * Enforce idle session timeout.
     *
     * @param  Request  $request
     * @return void
     */
    private function checkIdleTimeout(Request $request): void
    {
        $lastActive = Session::get('_last_active');

        if ($lastActive === null) {
            return;
        }

        $idleSeconds = now()->timestamp - $lastActive;

        if ($idleSeconds > ($this->idleTimeoutMinutes * 60)) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            if ($request->expectsJson()) {
                abort(401, 'Session expired due to inactivity.');
            }

            redirect()->route('login')
                ->with('error', 'Your session has expired. Please log in again.')
                ->send();
            exit;
        }
    }

    /**
     * Build a session fingerprint from the request's IP and User-Agent.
     * This ties the session to a specific client without storing sensitive data.
     *
     * @param  Request  $request
     * @return string
     */
    private function buildFingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent() ?? 'unknown',
        ]));
    }
}
