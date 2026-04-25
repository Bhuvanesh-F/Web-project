<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * EncryptCookies
 * Encrypts all outgoing cookies and decrypts incoming ones.
 * Prevents cookie tampering — important for session security.
 */
class EncryptCookies extends Middleware
{
    /**
     * Cookies that should NOT be encrypted.
     * Keep this list empty unless a specific third-party requires plain cookies.
     *
     * @var array<int, string>
     */
    protected $except = [];
}
