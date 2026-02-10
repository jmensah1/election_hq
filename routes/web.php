<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;

// 1. Remove ->name('login') from the root route
Route::get('/', function () {
    return view('welcome');
});

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

 

// 2. Create a dedicated login route that shows the login page
Route::get('/login', function () {
    if (! current_organization()) {
        return redirect('/');
    }
    return view('auth.login');
})->name('login');

// Authentication Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::middleware('throttle:oauth')->get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

if (app()->isLocal()) {
    Route::get('/dev/login', [App\Http\Controllers\Auth\DevAuthController::class, 'show'])->name('dev.login');
    Route::post('/dev/login', [App\Http\Controllers\Auth\DevAuthController::class, 'login']);
}


// Candidate Portal
Route::middleware(['auth', 'web'])->get('/candidate-portal', App\Livewire\CandidatePortal::class)->name('candidate.portal');

// Self-Nomination Portal
Route::middleware(['auth', 'web'])->get('/self-nominate', App\Livewire\SelfNomination::class)->name('self.nominate');

// Voter Dashboard
Route::middleware(['auth'])->prefix('vote')->name('voter.')->group(function () {
    Route::get('/elections', [App\Http\Controllers\Voter\VotingController::class, 'index'])->name('elections.index');
    Route::middleware('throttle:vote')->post('/elections/{election}', [App\Http\Controllers\Voter\VotingController::class, 'store'])->name('elections.store');
    Route::get('/elections/{election}/vote', [App\Http\Controllers\Voter\VotingController::class, 'show'])->name('elections.show');
    Route::get('/confirmation', [App\Http\Controllers\Voter\VotingController::class, 'confirmation'])->name('confirmation');
    Route::get('/results', [App\Http\Controllers\Voter\VotingController::class, 'published_results'])->name('published_results');
    Route::get('/results/{election}', [App\Http\Controllers\Voter\VotingController::class, 'results'])->name('results');
});

// Admin Print Route
Route::middleware(['auth'])->get('/admin/elections/{election}/print', App\Http\Controllers\Admin\PrintElectionResultsController::class)->name('admin.elections.print');

// Onboarding Flow
Route::get('/get-started', [App\Http\Controllers\OnboardingController::class, 'create'])->name('onboarding.create');
Route::post('/get-started', [App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');
Route::get('/get-started/success', [App\Http\Controllers\OnboardingController::class, 'success'])->name('onboarding.success');
Route::get('/get-started/payment/callback', [App\Http\Controllers\OnboardingController::class, 'paymentCallback'])->name('onboarding.payment.callback');
Route::get('/get-started/payment/cancelled', [App\Http\Controllers\OnboardingController::class, 'paymentCancelled'])->name('onboarding.payment.cancelled');

// Organization Setup (after payment)
Route::get('/get-started/setup/{payment}', [App\Http\Controllers\OnboardingController::class, 'setupChoice'])->name('onboarding.setup.choice');
Route::get('/get-started/setup/{payment}/form', [App\Http\Controllers\OnboardingController::class, 'setupOrganization'])->name('onboarding.setup.form');
Route::post('/get-started/setup/{payment}', [App\Http\Controllers\OnboardingController::class, 'storeOrganization'])->name('onboarding.setup.store');
Route::post('/get-started/setup/{payment}/skip', [App\Http\Controllers\OnboardingController::class, 'skipSetup'])->name('onboarding.setup.skip');

// Paystack Webhook (exclude from CSRF verification)
Route::post('/webhooks/paystack', [App\Http\Controllers\PaystackWebhookController::class, 'handle'])->name('webhooks.paystack');