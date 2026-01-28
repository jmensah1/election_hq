<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('current_organization') && $organization = current_organization()) {
            if ($organization->timezone) {
                config(['app.timezone' => $organization->timezone]);
                date_default_timezone_set($organization->timezone);
            }
        }

        return $next($request);
    }
}
