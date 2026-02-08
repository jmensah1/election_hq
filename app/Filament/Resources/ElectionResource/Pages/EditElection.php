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

    protected array $originalData = [];

    protected function beforeSave(): void
    {
        $this->originalData = $this->record->toArray();
    }

    protected function afterSave(): void
    {
        $election = $this->record;
        
        // Get changed attributes by comparing new state with captured original state
        $newData = $election->toArray();
        $changes = [];
        $oldValues = [];

        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $this->originalData) && $this->originalData[$key] !== $value) {
                $changes[$key] = $value;
                $oldValues[$key] = $this->originalData[$key];
            }
        }
        
        // Filter out timestamps if desired, but good to keep
        if (!empty($changes)) {
            app(\App\Services\AuditService::class)->log(
                action: 'Election Updated',
                entityType: \App\Models\Election::class,
                entityId: $election->id,
                oldValues: $oldValues,
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
