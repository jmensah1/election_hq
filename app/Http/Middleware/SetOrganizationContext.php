<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $organization = null;
        
        // 1. Check for Custom Domain
        $organization = Organization::where('custom_domain', $host)
            ->where('status', 'active')
            ->first();

        // 2. Check for Subdomain if no custom domain match
        if (! $organization) {
            // Strip scheme and path, just get host. app url e.g. "elections-hq.me"
            $appUrl = config('app.url');
            // If APP_URL is not set or parse fails, fallback might be needed, but assume valid env.
            $appHost = parse_url($appUrl, PHP_URL_HOST);

            if ($appHost && str_ends_with($host, $appHost)) {
                // e.g. "school.elections-hq.me" -> subdomain = "school"
                // e.g. "elections-hq.me" -> subdomain = null (root)
                
                // If host is exactly appHost, subdomain is empty.
                if ($host !== $appHost) {
                     $subdomain = str_replace('.' . $appHost, '', $host);
                     if (!empty($subdomain) && $subdomain !== $host) {
                        $organization = Organization::where('subdomain', $subdomain)
                            ->where('status', 'active')
                            ->first();
                     }
                }
            }
        }

        if ($organization) {
            // Bind to the container as a singleton for this request
            app()->instance('current_organization', $organization);
            
            // Share with all views
            View::share('currentOrganization', $organization);
        } else {
            // Optional: If we are on a tenant subdomain but organization not found/inactive -> 404
            // But if we are on root domain (landing page), we proceed without org.
            
            $appUrl = config('app.url');
            $appHost = parse_url($appUrl, PHP_URL_HOST);
            
            // If we are strictly NOT on the main domain, and found no org, it's a 404.
            if ($appHost && $host !== $appHost && !$organization) {
                 abort(404, 'Organization not found.');
            }
        }

        return $next($request);
    }
}
