<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;

// 1. Remove ->name('login') from the root route
Route::get('/', function () {
    return view('welcome');
}); 

// 2. Create a dedicated login route that triggers the Google Redirect
Route::get('/login', [GoogleAuthController::class, 'redirect'])->name('login');

// Authentication Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');


// Candidate Portal
Route::middleware(['auth', 'web'])->get('/candidate-portal', App\Livewire\CandidatePortal::class)->name('candidate.portal');

// Voter Dashboard
Route::middleware(['auth'])->prefix('vote')->name('voter.')->group(function () {
    Route::get('/elections', [App\Http\Controllers\Voter\VotingController::class, 'index'])->name('elections.index');
    Route::get('/elections/{election}/vote', [App\Http\Controllers\Voter\VotingController::class, 'show'])->name('elections.show');
    Route::get('/confirmation', [App\Http\Controllers\Voter\VotingController::class, 'confirmation'])->name('confirmation');
    Route::get('/results/{election}', [App\Http\Controllers\Voter\VotingController::class, 'results'])->name('results');
});