<?php

namespace App\Listeners;

use App\Services\AuditService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        // $event->user is the authenticated user
        $this->auditService->log(
            action: 'login',
            entityType: get_class($event->user),
            entityId: $event->user->id,
            oldValues: null,
            newValues: null,
            orgId: null // Service will infer or usage of trait handles it
        );
    }
}
