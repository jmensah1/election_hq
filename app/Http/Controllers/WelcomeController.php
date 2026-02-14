<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        // Get organization from subdomain
        $organization = null;
        $host = $request->getHost();
        
        // Use configured base domain, or auto-detect from current request
        $baseDomain = config('app.base_domain');
        
        if (!$baseDomain) {
            // Auto-detect: if host is subdomain.example.com, base is example.com
            $parts = explode('.', $host);
            $baseDomain = count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host;
        }
        
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            if ($subdomain && $subdomain !== 'www') {
                $organization = Organization::where('subdomain', $subdomain)->first();
            }
        }
        
        $brandName = $organization?->name ?? 'Elections HQ';
        $logoUrl = $organization?->logo_path ? asset('storage/' . $organization->logo_path) : null;

        return view('welcome', compact('organization', 'brandName', 'logoUrl'));
    }
}
