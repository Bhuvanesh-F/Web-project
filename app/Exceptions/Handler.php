<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exception Handler
 *
 * Customises how exceptions are reported and rendered.
 * API requests always receive JSON error responses.
 * Web requests receive Blade error views.
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class Handler extends ExceptionHandler
{
    /**
     * Exception types that are not reported to logs.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Input fields whose values are never included in logs.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Custom reporting logic can be added here
        });

        // Return JSON for API unauthenticated exceptions
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in.',
                ], 401);
            }
        });

        // Return JSON for API validation exceptions
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Custom 404 page
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if (! $request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found.',
            ], 404);
        });
    }
}
