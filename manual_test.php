<?php

$logFile = __DIR__ . '/verify_phase3.txt';
file_put_contents($logFile, "Starting manual test at " . date('Y-m-d H:i:s') . "...\n");

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    file_put_contents($logFile, "App booted.\n", FILE_APPEND);

    // Mock Request 1: Subdomain
    file_put_contents($logFile, "Testing Subdomain...\n", FILE_APPEND);
    
    // Check if org exists first to avoid dupes in repeated runs or just creating distinct
    $org = \App\Models\Organization::where('subdomain', 'manualtest')->first();
    if (!$org) {
        $org = new \App\Models\Organization();
        $org->name = 'Manual Test Org';
        $org->slug = 'manual-test-org';
        $org->subdomain = 'manualtest';
        $org->timezone = 'America/New_York';
        $org->status = 'active';
        $org->save();
    }
    
    // Create request
    $request = \Illuminate\Http\Request::create('http://manualtest.elections-hq.me', 'GET');
    
    // Handle request through middleware
    $response = $kernel->handle($request);
    
    $currentOrg = app('current_organization');
    
    if ($currentOrg && $currentOrg->id === $org->id) {
        file_put_contents($logFile, "PASS: Subdomain resolved correctly.\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "FAIL: Subdomain resolution failed. Got: " . ($currentOrg ? $currentOrg->name : 'NULL') . "\n", FILE_APPEND);
    }
    
    if (config('app.timezone') === 'America/New_York') {
         file_put_contents($logFile, "PASS: Timezone set correctly.\n", FILE_APPEND);
    } else {
         file_put_contents($logFile, "FAIL: Timezone wrong. Got: " . config('app.timezone') . "\n", FILE_APPEND);
    }

    file_put_contents($logFile, "Test Complete.\n", FILE_APPEND);

} catch (\Throwable $e) {
    file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString(), FILE_APPEND);
}
