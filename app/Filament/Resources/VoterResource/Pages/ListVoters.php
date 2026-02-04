<?php

namespace App\Filament\Resources\VoterResource\Pages;

use App\Filament\Resources\VoterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\OrganizationUser;
// We need an Import class, or inline logic. MVP inline logic using simple CSV parsing.

class ListVoters extends ListRecords
{
    protected static string $resource = VoterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('importVoters')
                ->label('Import Voters (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('Upload CSV (Columns: voter_id, email, [role], [department])')
                        ->disk('public')
                        ->directory('imports')
                        ->preserveFilenames()
                        ->previewable(false)
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain', 
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                        ])
                        ->maxSize(5120) // 5MB max
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Logic to handle import will be here.
                    // For now, simpler placeholder to avoid complexity in this file create step.
                    // We can implement the CSV parsing logic in a Service or inline.
                    $this->importVoters($data['csv_file']);
                }),
        ];
    }
    
    protected function importVoters($filePath)
    {
        $file = storage_path('app/public/' . $filePath);
        
        if (!file_exists($file)) {
             Notification::make()->title('File not found')->danger()->send();
             return;
        }
        
        $handle = fopen($file, "r");
        $header = fgetcsv($handle); // Skip header (assuming standard format)
        
        // Simple mapping: 0=voter_id, 1=email
        $count = 0;
        $errors = 0;
        $orgId = current_organization_id() ?? auth()->user()->organization_id;

        if (!$orgId) {
             Notification::make()->title('No organization context detected')->danger()->send();
             return;
        }

        /** @var \App\Services\AuditService $auditService */
        $auditService = app(\App\Services\AuditService::class);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 2) continue;
            
            $voterId = trim($data[0]);
            $email = trim($data[1]);
            
            if (empty($voterId) || empty($email)) continue;

            try {
                $voter = OrganizationUser::where('organization_id', $orgId)
                    ->where('voter_id', $voterId)
                    ->first();

                if ($voter) {
                    // Update existing
                    $oldValues = $voter->getAttributes();
                    
                    $voter->allowed_email = $email;
                    // 'role' => 'voter', // Default - usually we don't overwrite role on simple import unless specified
                    // 'status' => 'pending', // Default - don't reset status if active
                    
                    if ($voter->isDirty()) {
                        $voter->save();
                        
                        $newValues = $voter->getAttributes();
                        $changes = [];
                        $original = [];

                        foreach ($newValues as $key => $value) {
                            if (array_key_exists($key, $oldValues) && $oldValues[$key] !== $value) {
                                $changes[$key] = $value;
                                $original[$key] = $oldValues[$key];
                            }
                        }

                        $auditService->log(
                            action: 'voter.imported_updated',
                            entityType: OrganizationUser::class,
                            entityId: $voter->id,
                            oldValues: $original,
                            newValues: $changes,
                            orgId: $orgId
                        );
                    }
                } else {
                    // Create new
                    $voter = OrganizationUser::create([
                        'organization_id' => $orgId,
                        'voter_id' => $voterId,
                        'allowed_email' => $email,
                         'role' => 'voter', 
                         'status' => 'pending', 
                    ]);

                    $auditService->log(
                        action: 'voter.imported_created',
                        entityType: OrganizationUser::class,
                        entityId: $voter->id,
                        newValues: $voter->toArray(),
                        orgId: $orgId
                    );
                }
                
                $count++;
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        fclose($handle);
        
        // Clean up file
        unlink($file);
        
        Notification::make()
            ->title("Imported {$count} voters successfully" . ($errors > 0 ? " ({$errors} errors)" : ""))
            ->success()
            ->send();
    }
}
