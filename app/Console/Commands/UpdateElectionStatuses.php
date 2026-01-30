<?php

namespace App\Console\Commands;

use App\Models\Election;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateElectionStatuses extends Command
{
    protected $signature = 'elections:update-statuses';
    
    protected $description = 'Automatically transition election statuses based on timeline dates';

    public function handle(): int
    {
        $now = Carbon::now();
        $transitioned = 0;

        // Get all non-terminal elections
        $elections = Election::whereNotIn('status', ['completed', 'cancelled'])->get();

        foreach ($elections as $election) {
            $oldStatus = $election->status;
            $newStatus = $this->determineStatus($election, $now);

            if ($newStatus && $newStatus !== $oldStatus) {
                $election->update(['status' => $newStatus]);
                $transitioned++;
                $this->info("Election '{$election->title}' transitioned: {$oldStatus} → {$newStatus}");
            }
        }

        $this->info("Checked " . $elections->count() . " elections, transitioned {$transitioned}.");
        
        return Command::SUCCESS;
    }

    protected function determineStatus(Election $election, Carbon $now): ?string
    {
        // Check in reverse order (most advanced status first)
        
        // Voting period ended → completed
        if ($election->voting_end_date && $now->gte($election->voting_end_date)) {
            return 'completed';
        }

        // Voting period started → voting
        if ($election->voting_start_date && $now->gte($election->voting_start_date)) {
            return 'voting';
        }

        // Vetting period started → vetting
        if ($election->vetting_start_date && $now->gte($election->vetting_start_date)) {
            return 'vetting';
        }

        // Nomination period started → nomination
        if ($election->nomination_start_date && $now->gte($election->nomination_start_date)) {
            return 'nomination';
        }

        // Still in draft (before nomination starts)
        return null;
    }
}
