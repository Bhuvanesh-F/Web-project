<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SanitizeInput Middleware
 *
 * Strips XSS payloads from all incoming request data before it
 * reaches controllers or validation layers.
 *
 * Security approach:
 * - htmlspecialchars() with ENT_QUOTES | ENT_HTML5 converts dangerous
 *   characters (<, >, ", ', &) to their HTML entities.
 * - strip_tags() removes any remaining HTML/PHP tags.
 * - Applied recursively to arrays (nested form data).
 *
 * Note: This is a defence-in-depth measure. Eloquent's parameterised
 * queries already prevent SQL injection. Blade's {{ }} auto-escapes
 * output. This middleware adds an extra layer for raw input.
 */
class SanitizeInput
{
    /**
     * Fields that should NOT be sanitized (e.g. passwords, tokens).
     * Sanitizing passwords would corrupt bcrypt comparisons.
     *
     * @var array<string>
     */
    protected array $exemptFields = [
        'password',
        'password_confirmation',
        'current_password',
        '_token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only sanitize state-changing requests with body data
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $sanitized = $this->sanitizeData($request->all());
            $request->merge($sanitized);
        }

        return $next($request);
    }

    /**
     * Recursively sanitize an array of input data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Skip exempt fields
            if (in_array($key, $this->exemptFields, true)) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                // Numeric, boolean, null — pass through as-is
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a single string value against XSS.
     *
     * @param  string  $value
     * @return string
     */
    private function sanitizeString(string $value): string
    {
        // Step 1: Remove HTML/PHP tags entirely
        $value = strip_tags($value);

        // Step 2: Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Step 3: Remove null bytes (another common injection vector)
        $value = str_replace("\0", '', $value);

        return $value;
    }
}
