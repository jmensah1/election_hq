<?php

namespace App\Filament\Resources\ElectionResource\Pages;

use App\Filament\Resources\ElectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElection extends EditRecord
{
    protected static string $resource = ElectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    app(\App\Services\AuditService::class)->log(
                        action: 'election_deleted',
                        entityType: \App\Models\Election::class,
                        entityId: $record->id,
                        oldValues: $record->toArray(),
                        orgId: $record->organization_id
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        $election = $this->record;
        
        // Get changed attributes
        $changes = $election->getChanges();
        $original = $election->getOriginal();
        
        // Filter out timestamps if desired, but good to keep
        if (!empty($changes)) {
            app(\App\Services\AuditService::class)->log(
                action: 'election_updated',
                entityType: \App\Models\Election::class,
                entityId: $election->id,
                oldValues: array_intersect_key($original, $changes),
                newValues: $changes,
                orgId: $election->organization_id
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
