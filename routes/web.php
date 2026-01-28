<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-tenancy', function () {
    return view('debug-tenancy');
});

// Helper to create a test org easily (Verification only)
Route::get('/create-test-org', function () {
    $org = \App\Models\Organization::firstOrCreate(
        ['subdomain' => 'test'],
        [
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'timezone' => 'America/Chicago', // Different from UTC to prove change
            'status' => 'active'
        ]
    );
    
    return redirect()->to('http://test.elections-hq.me/debug-tenancy');
});
