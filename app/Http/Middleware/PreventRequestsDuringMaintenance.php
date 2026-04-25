<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs that should be reachable even in maintenance mode.
     * Health check endpoint stays up so monitoring knows the app is in maintenance.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/health',
    ];
}
