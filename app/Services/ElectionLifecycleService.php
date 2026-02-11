<?php

namespace App\Services;

use App\Models\Election;
use App\Models\User;
use InvalidArgumentException;

class ElectionLifecycleService
{
    /**
     * Transition election to a new status.
     *
     * @param Election $election
     * @param string $newStatus
     * @return bool
     * @throws InvalidArgumentException
     */
    public function transitionStatus(Election $election, string $newStatus): bool
    {
        $validStatuses = ['draft', 'nomination', 'vetting', 'voting', 'completed', 'cancelled'];
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new InvalidArgumentException("Invalid status: $newStatus");
        }

        // Validate Transition Logic (simplified state machine)
        // e.g. draft -> nomination -> vetting -> voting -> completed
        // cancelled can be from any state
        
        $currentStatus = $election->status;
        
        if ($newStatus === 'cancelled') {
            $election->update(['status' => 'cancelled']);
            return true;
        }

        // Strict flow enforcement
        $allowedTransitions = [
            'draft' => ['nomination'],
            'nomination' => ['vetting'],
            'vetting' => ['voting'],
            'voting' => ['completed'],
            'completed' => [], // Terminal state
            'cancelled' => [], // Terminal state
        ];

        // Allow 'draft' as a reset from others during dev? No, stricter for production.
        // Actually admins might need to rollback. For MVP let's be strict but allow 'draft' from anywhere if needed?
        // Architecture doc says: "Transitions must follow this order (no skipping, no going back except to cancelled)."
        
        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
             // For Admin convenience, maybe we allow 'draft' -> 'voting' directly if dates passed?
             // Architecture says: "Transitions must follow this order". So we strict validation.
             // But if current status invalid, we might be stuck.
             
             // Let's implement simpler check: just update it. Admin UI performs validation.
             // But the service should enforce rules.
             
             // Throw exception if invalid transition
             throw new InvalidArgumentException("Invalid transition from $currentStatus to $newStatus");
        }

        // Wrap status update and side effects in a transaction for atomicity
        return \DB::transaction(function () use ($election, $newStatus) {
            $election->update(['status' => $newStatus]);
            
            switch($newStatus) {
                case 'voting': 
                    // NotificationService::notifyVotersStart($election); 
                    break;
                case 'completed': 
                    app(\App\Services\ResultsService::class)->determineWinners($election); 
                    break;
            }
            
            return true;
        });
    }
}
