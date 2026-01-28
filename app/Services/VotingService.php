<?php

namespace App\Services;

use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AlreadyVotedException;
use App\Exceptions\ElectionNotOpenException;

class VotingService
{
    public function castVote(Election $election, User $user, array $ballot): void
    {
        // 1. Global Checks (Logic overlapping with Policy but good for double safety if used outside HTTP)
        if ($election->status !== 'voting') {
            throw new ElectionNotOpenException("Election is not open.");
        }
        
        // Ballot format: [position_id => candidate_id]

        DB::transaction(function () use ($election, $user, $ballot) {
            foreach ($ballot as $positionId => $candidateId) {
                // 2. Vote Prevention: Check if already voted for this position
                // We lock the row or use unique constraint. 
                // DB unique constraint on vote_confirmations (election_id, position_id, user_id) handles race conditions.
                // However, we can check first for a cleaner error message.
                
                try {
                    VoteConfirmation::create([
                        'position_id' => $positionId,
                        'user_id' => $user->id,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    throw new AlreadyVotedException("You have already voted for position ID: $positionId");
                }

                // 3. Create Vote Confirmation (WHO voted)
                VoteConfirmation::create([
                    'organization_id' => $election->organization_id,
                    'election_id' => $election->id,
                    'position_id' => $positionId,
                    'user_id' => $user->id,
                    'voted_at' => now(),
                    'ip_address' => request()->ip(), // Service accessing request() is debatable but practical here
                    'user_agent' => request()->userAgent(),
                ]);

                // 4. Create Vote (WHAT was voted - Anonymous)
                Vote::create([
                    'organization_id' => $election->organization_id,
                    'election_id' => $election->id,
                    'position_id' => $positionId,
                    'candidate_id' => $candidateId,
                    // NO user_id
                    // NO timestamps (if configured in model)
                ]);
            }
        });
        
        // 5. Dispatch Confirmation Job
        // \App\Jobs\SendVoteConfirmation::dispatch($user, $election);
        
        // 6. Audit Log handled by AuditService or observers (deferred)
    }
}
