<?php

namespace App\Services;

use App\Models\Election;
use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendVoteConfirmation;

class NotificationService
{
    /**
     * Send a vote confirmation email to the user.
     */
    public function sendVoteConfirmation(User $user, Election $election): void
    {
        // 1. Create Notification Record
        $notification = Notification::create([
            'organization_id' => $election->organization_id,
            'user_id' => $user->id,
            'election_id' => $election->id,
            'type' => 'email',
            'category' => 'vote_confirmation',
            'recipient' => $user->email,
            'subject' => "Vote Confirmation: {$election->title}",
            'message' => "Vote confirmation for {$election->title}", // Simplified for log
            'status' => 'pending',
        ]);

        // 2. Dispatch Job
        SendVoteConfirmation::dispatch($user, $election, $notification);
    }

    /**
     * Send election reminders to all eligible voters who haven't voted.
     * (Deferred Implementation)
     */
    public function sendElectionReminder(Election $election): void
    {
        // @todo Implement in future phase
    }

    /**
     * Send results announcement to all voters.
     * (Deferred Implementation)
     */
    public function sendResultsAnnouncement(Election $election): void
    {
        // @todo Implement in future phase
    }
}
