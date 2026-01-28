<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
})->name('login'); // Name welcome as login for redirect fallback

// Authentication Routes
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/logout', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'logout'])->name('logout');


// Voter Dashboard
Route::middleware(['auth'])->prefix('vote')->name('voter.')->group(function () {
    Route::get('/elections', function () {
        return view('voter.dashboard');
    })->name('elections.index');
});

// Route::get('/debug-tenancy', function () {
//     return view('debug-tenancy');
// });

// Helper to create a test org easily (Verification only)
// Route::get('/create-test-org', function () {
//     $org = \App\Models\Organization::firstOrCreate(
//         ['subdomain' => 'test'],
//         [
//             'name' => 'Test Organization',
//             'slug' => 'test-organization',
//             'timezone' => 'America/Chicago', // Different from UTC to prove change
//             'status' => 'active'
//         ]
//     );
//    
//     return redirect()->to('http://test.elections-hq.me/debug-tenancy');
// });
