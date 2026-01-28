<?php

use Illuminate\Support\Facades\Route;
use App\Models\Organization;

Route::get('/fix-timezone', function () {
    $org = Organization::where('subdomain', 'upsa')->first();
    if ($org) {
        $old = $org->timezone;
        $org->timezone = 'UTC';
        $org->save();
        return "Fixed timezone for {$org->name}. Old: " . json_encode($old) . ", New: UTC";
    }
    return "Organization not found";
});
