<?php

namespace App\Console\Commands;

use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateResults extends Command
{
    protected $signature = 'elections:recalculate-results {election_id? : Optional ID of specific election}';
    
    protected $description = 'Recalculate vote counts for candidates based on actual votes cast';

    public function handle(): int
    {
        $electionId = $this->argument('election_id');

        $query = Election::query();
        if ($electionId) {
            $query->where('id', $electionId);
        }

        $elections = $query->get();
        $this->info("Found " . $elections->count() . " elections to process.");

        foreach ($elections as $election) {
            $this->info("Processing Election: {$election->title} (ID: {$election->id})");
            
            DB::transaction(function () use ($election) {
                // Reset all counts first
                Candidate::where('election_id', $election->id)->update(['vote_count' => 0]);
                
                // Get aggregate counts from votes table
                $counts = Vote::where('election_id', $election->id)
                    ->where('is_no_vote', false)
                    ->select('candidate_id', DB::raw('count(*) as total'))
                    ->groupBy('candidate_id')
                    ->get();

                $updated = 0;
                foreach ($counts as $count) {
                    Candidate::where('id', $count->candidate_id)
                        ->update(['vote_count' => $count->total]);
                    $updated++;
                }
                
                $this->info("  - Updated counts for $updated candidates.");
            });
        }

        $this->info("Recalculation complete.");
        return Command::SUCCESS;
    }
}
