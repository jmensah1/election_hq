<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()?->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $data['organization_id'] = current_organization_id();
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $candidate = $this->record;
        
        // Log the activity
        app(\App\Services\AuditService::class)->log(
            action: 'candidate_created',
            entityType: \App\Models\Candidate::class,
            entityId: $candidate->id,
            newValues: $candidate->toArray(),
            orgId: $candidate->organization_id
        );

        // Send invitation email
        try {
            \Illuminate\Support\Facades\Mail::to($candidate->email)
                ->send(new \App\Mail\CandidateInvitation($candidate));
            
            \Filament\Notifications\Notification::make()
                ->title('Invitation Sent')
                ->body("An invitation email has been sent to {$candidate->email}.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Failed to send invitation')
                ->body("Candidate created, but email failed: " . $e->getMessage())
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
