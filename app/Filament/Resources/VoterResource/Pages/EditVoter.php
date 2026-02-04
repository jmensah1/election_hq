<?php

namespace App\Filament\Resources\VoterResource\Pages;

use App\Filament\Resources\VoterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVoter extends EditRecord
{
    protected static string $resource = VoterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $oldValues = $record->getAttributes();
        
        $record->update($data);
        
        $newValues = $record->getAttributes();
        
        // Calculate changes
        $changes = [];
        $original = [];
        
        foreach ($newValues as $key => $value) {
            if (array_key_exists($key, $oldValues) && $oldValues[$key] !== $value) {
                $changes[$key] = $value;
                $original[$key] = $oldValues[$key];
            }
        }
        
        if (!empty($changes)) {
            /** @var \App\Services\AuditService $auditService */
            $auditService = app(\App\Services\AuditService::class);
            
            $auditService->log(
                action: 'voter.updated',
                entityType: \App\Models\OrganizationUser::class,
                entityId: $record->id,
                oldValues: $original,
                newValues: $changes,
                orgId: $record->organization_id
            );
        }
        
        return $record;
    }
}
