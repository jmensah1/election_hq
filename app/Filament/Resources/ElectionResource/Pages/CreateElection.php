<?php

namespace App\Filament\Resources\ElectionResource\Pages;

use App\Filament\Resources\ElectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElection extends CreateRecord
{
    protected static string $resource = ElectionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $data['organization_id'] = current_organization_id();
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $election = $this->record;
        
        // Log the activity
        app(\App\Services\AuditService::class)->log(
            action: 'election_created',
            entityType: \App\Models\Election::class,
            entityId: $election->id,
            newValues: $election->toArray(),
            orgId: $election->organization_id
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
