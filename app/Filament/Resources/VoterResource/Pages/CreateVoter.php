<?php

namespace App\Filament\Resources\VoterResource\Pages;

use App\Filament\Resources\VoterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoter extends CreateRecord
{
    protected static string $resource = VoterResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $data['organization_id'] = current_organization_id();
        }

        $orgId = $data['organization_id'] ?? null;
        if ($orgId) {
            $org = \App\Models\Organization::find($orgId);
            $planService = app(\App\Services\PlanLimitService::class);
            
            if ($org && !$planService->canAddVoter($org)) {
                $message = $planService->getLimitMessage('voters', $org); // Removed $feature
                
                \Filament\Notifications\Notification::make()
                    ->title('Plan Limit Reached')
                    ->body($message)
                    ->danger()
                    ->persistent()
                    ->send();
                
                $this->halt();
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Log the creation
        $voter = $this->record;
        
        /** @var \App\Services\AuditService $auditService */
        $auditService = app(\App\Services\AuditService::class);
        
        $auditService->log(
            action: 'voter.created',
            entityType: \App\Models\OrganizationUser::class,
            entityId: $voter->id,
            newValues: $voter->toArray(),
            orgId: $voter->organization_id
        );
    }
}
