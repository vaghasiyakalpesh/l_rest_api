<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassMaintenanceForApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance mode check for api routes
        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }
        // Default laravel maintence mode check
        if (app()->isDownForMaintenance()) {
            abort(503);
        }
        return $next($request);
    }

    protected function shouldBypassMaintenance(Request $request)
    {
        // Bypass for all api routes (modify if needed)
        return $request->is('api/*') || $request->expectsJson();
    }
}
