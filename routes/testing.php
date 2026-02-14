<?php

use Illuminate\Support\Facades\Route;

/**
 * Development-only routes for testing email templates and authentication.
 */

Route::get('/dev/login', [App\Http\Controllers\Auth\DevAuthController::class, 'show'])->name('dev.login');
Route::post('/dev/login', [App\Http\Controllers\Auth\DevAuthController::class, 'login']);

Route::get('/test-emails', function () {
    return '
    <h1>Email Previews</h1>
    <ul>
        <li><a href="/test-emails/candidate-invitation">Candidate Invitation</a></li>
        <li><a href="/test-emails/vote-confirmation">Vote Confirmation</a></li>
        <li><a href="/test-emails/lead-received">Lead Received Confirmation</a></li>
        <li><a href="/test-emails/new-lead-admin">New Lead Admin Notification</a></li>
        <li><a href="/test-emails/contact-submitted">Contact Form Submitted</a></li>
    </ul>
    ';
});

Route::get('/test-emails/candidate-invitation', function () {
    $candidate = \App\Models\Candidate::first();
    if (!$candidate) {
        $candidate = new \App\Models\Candidate([
            'email' => 'candidate@example.com',
        ]);
        $candidate->setRelation('position', new \App\Models\Position(['name' => 'President']));
        $candidate->setRelation('election', new \App\Models\Election(['title' => '2025 General Election']));
    }
    return new \App\Mail\CandidateInvitation($candidate);
});

Route::get('/test-emails/vote-confirmation', function () {
    $user = \App\Models\User::first() ?? new \App\Models\User(['name' => 'John Doe', 'email' => 'john@example.com']);
    $election = \App\Models\Election::first() ?? new \App\Models\Election(['title' => '2025 General Election']);

    return new \App\Mail\VoteConfirmation($user, $election);
});

Route::get('/test-emails/lead-received', function () {
    $lead = \App\Models\Lead::first() ?? new \App\Models\Lead([
        'name' => 'Jane Doe',
        'organization_name' => 'Acme Corp',
        'email' => 'jane@acme.com',
        'plan_tier' => 'pro',
        'billing_cycle' => 'yearly'
    ]);
    return new \App\Mail\LeadReceivedConfirmation($lead);
});

Route::get('/test-emails/new-lead-admin', function () {
    $lead = \App\Models\Lead::first() ?? new \App\Models\Lead([
        'name' => 'Jane Doe',
        'organization_name' => 'Acme Corp',
        'email' => 'jane@acme.com',
        'phone' => '123-456-7890',
        'plan_tier' => 'pro',
        'billing_cycle' => 'yearly',
        'message' => 'Interested in the enterprise plan features.'
    ]);
    return new \App\Mail\NewLeadAdminNotification($lead);
});

Route::get('/test-emails/contact-submitted', function () {
    $data = [
        'name' => 'John Smith',
        'email' => 'john@smith.com',
        'subject' => 'Inquiry about pricing',
        'message' => 'Hello, I would like to know more about your pricing structure.'
    ];
    return new \App\Mail\ContactFormSubmitted($data);
});
