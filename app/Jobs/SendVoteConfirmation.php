<?php

namespace App\Jobs;

use App\Mail\VoteConfirmation;
use App\Models\Election;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVoteConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $election;
    public $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Election $election, ?Notification $notification = null)
    {
        $this->user = $user;
        $this->election = $election;
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user)->send(new VoteConfirmation($this->user, $this->election));

            if ($this->notification) {
                $this->notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            if ($this->notification) {
                $this->notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
            
            throw $e; // Retry the job
        }
    }
}
