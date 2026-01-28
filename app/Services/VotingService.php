<?php

namespace App\Services;

use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AlreadyVotedException;
use App\Exceptions\ElectionNotOpenException;
use Illuminate\Database\QueryException;

class VotingService
{
    protected $auditService;
    protected $notificationService;

    public function __construct(AuditService $auditService, NotificationService $notificationService)
    {
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function castVote(Election $election, User $user, array $ballot): void
    {
        // 1. Global Checks
        if ($election->status !== 'voting') {
            throw new ElectionNotOpenException("Election is not open.");
        }
        
        // Ballot format: [position_id => candidate_id]

        DB::transaction(function () use ($election, $user, $ballot) {
            foreach ($ballot as $positionId => $candidateId) {
                // 2. Vote Prevention & Confirmation (Atomic)
                // We rely on the Unique Constraint on the vote_confirmations table.
                // (election_id, position_id, user_id)
                
                try {
                    // Create Vote Confirmation (WHO voted)
                    VoteConfirmation::create([
                        'organization_id' => $election->organization_id,
                        'election_id' => $election->id,
                        'position_id' => $positionId,
                        'user_id' => $user->id,
                        'voted_at' => now(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                } catch (QueryException $e) {
                    // Unique constraint violation (23000 = duplicate entry)
                    if ($e->getCode() == 23000) {
                        throw new AlreadyVotedException("You have already voted for position ID: $positionId");
                    }
                    throw $e;
                }

                // 3. Create Vote (WHAT was voted - Anonymous)
                Vote::create([
                    'organization_id' => $election->organization_id,
                    'election_id' => $election->id,
                    'position_id' => $positionId,
                    'candidate_id' => $candidateId,
                    // NO user_id, NO timestamps
                ]);
            }
            
            // 4. Audit Log
            $this->auditService->log(
                action: 'vote_cast',
                entityType: Election::class,
                entityId: $election->id,
                newValues: ['positions_count' => count($ballot)]
            );
        });
        
        // 5. Dispatch Confirmation Notification (Outside Transaction)
        // We do this after transaction commits to ensure we don't send email if DB fails
        $this->notificationService->sendVoteConfirmation($user, $election);
    }
}
