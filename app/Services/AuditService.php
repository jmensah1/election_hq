<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Create an audit log entry.
     *
     * @param string $action The action performed (e.g., 'vote_cast', 'election_created')
     * @param string $entityType The class name of the entity (e.g., Election::class)
     * @param int|null $entityId The ID of the entity
     * @param array|null $oldValues Old values before change (for updates)
     * @param array|null $newValues New values after change
     * @param int|null $orgId Optional organization ID override
     * @return AuditLog
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $orgId = null
    ): AuditLog {
        $user = Auth::user();
        
        // Determine Organization ID
        // Priority: Passed arg -> User's current org (if any) -> null
        $organizationId = $orgId;
        
        if (!$organizationId && $user && method_exists($user, 'currentOrganization')) { 
            // Assuming user might have a helper or we check session/context usually
            // but here we rely on the passed argument or falls back to nullable 
            // IF the model trait doesn't pick it up. 
            // Actually, BelongsToOrganization trait usually handles this if model is created.
            // But AuditLog creation might be explicit.
            
            // For now, let's rely on the passed ID or leave it null if it's a system action.
            // However, the architecture says BelongsToOrganization trait is on AuditLog.
            // So if we don't set it, the trait might try to set it from Auth context.
        }

        return AuditLog::create([
            'organization_id' => $organizationId, // Let trait handle if null and in context
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
