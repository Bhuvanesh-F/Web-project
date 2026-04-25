<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * TrustProxies
 * Configures trusted reverse proxies (Nginx, load balancers).
 * Ensures the real client IP is used in audit logs and rate limiting.
 */
class TrustProxies extends Middleware
{
    /**
     * Set to '*' if behind a load balancer that you fully control.
     * In production, restrict to specific proxy IPs.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR    |
        Request::HEADER_X_FORWARDED_HOST   |
        Request::HEADER_X_FORWARDED_PORT   |
        Request::HEADER_X_FORWARDED_PROTO  |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
