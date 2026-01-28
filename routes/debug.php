<?php

use Illuminate\Support\Facades\Route;
use App\Models\Organization;

Route::get('/debug-orgs', function () {
    return Organization::all(['id', 'name', 'slug', 'subdomain', 'status']);
});
