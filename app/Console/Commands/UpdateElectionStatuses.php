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
            $targetStatus = $this->determineStatus($election, $now);

            if ($targetStatus && $targetStatus !== $oldStatus) {
                try {
                    // Transition step-by-step through allowed states to reach target
                    $this->transitionToTargetStatus($election, $oldStatus, $targetStatus);
                    $transitioned++;
                    $this->info("Election '{$election->title}' transitioned: {$oldStatus} → {$targetStatus}");
                } catch (\Exception $e) {
                    $this->error("Failed to transition election '{$election->title}': " . $e->getMessage());
                }
            }
        }

        $this->info("Checked " . $elections->count() . " elections, transitioned {$transitioned}.");
        
        return Command::SUCCESS;
    }

    protected function transitionToTargetStatus(Election $election, string $currentStatus, string $targetStatus): void
    {
        // Define the ordered state progression
        $stateOrder = ['draft', 'nomination', 'vetting', 'voting', 'completed'];
        
        $currentIndex = array_search($currentStatus, $stateOrder);
        $targetIndex = array_search($targetStatus, $stateOrder);
        
        if ($currentIndex === false || $targetIndex === false) {
            throw new \Exception("Invalid status transition");
        }
        
        // If we need to skip states, transition through each intermediate state
        if ($targetIndex > $currentIndex) {
            $lifecycleService = app(\App\Services\ElectionLifecycleService::class);
            
            for ($i = $currentIndex + 1; $i <= $targetIndex; $i++) {
                $nextStatus = $stateOrder[$i];
                $lifecycleService->transitionStatus($election, $nextStatus);
                $election->refresh();
            }
        }
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
