<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

/**
 * TrimStrings
 * Trims whitespace from all incoming string request data.
 * Passwords are excluded to preserve exact values.
 */
class TrimStrings extends Middleware
{
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];
}
