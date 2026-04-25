<?php

namespace App\Providers;

use App\Services\AuditService;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 * Registers application-level service bindings.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind AuditService as a singleton so a single instance
        // is reused across all controllers in one request lifecycle.
        $this->app->singleton(AuditService::class, function () {
            return new AuditService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
