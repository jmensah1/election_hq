<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    app(\App\Services\AuditService::class)->log(
                        action: 'candidate_deleted',
                        entityType: \App\Models\Candidate::class,
                        entityId: $record->id,
                        oldValues: $record->toArray(),
                        orgId: $record->organization_id
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        $candidate = $this->record;
        
        // Get changed attributes
        $changes = $candidate->getChanges();
        $original = $candidate->getOriginal();
        
        // Filter out timestamps if desired, but good to keep
        if (!empty($changes)) {
            app(\App\Services\AuditService::class)->log(
                action: 'candidate_updated',
                entityType: \App\Models\Candidate::class,
                entityId: $candidate->id,
                oldValues: array_intersect_key($original, $changes),
                newValues: $changes,
                orgId: $candidate->organization_id
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
