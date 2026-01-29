<?php

namespace App\Listeners;

use App\Services\AuditService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogout
{
    protected $auditService;

    /**
     * Create the event listener.
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            $this->auditService->log(
                action: 'logout',
                entityType: get_class($event->user),
                entityId: $event->user->id,
                oldValues: null,
                newValues: null,
                orgId: null
            );
        }
    }
}
