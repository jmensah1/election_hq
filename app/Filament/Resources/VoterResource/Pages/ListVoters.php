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
                        ->label('Upload CSV (Columns: voter_id, email, [phone], [department])')
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
        
        // Simple mapping: 0=voter_id, 1=email, 2=phone, 3=department
        $count = 0;
        $errors = 0;
        $smsRequiredSkipped = 0;
        $orgId = current_organization_id() ?? auth()->user()->organization_id;

        if (!$orgId) {
             Notification::make()->title('No organization context detected')->danger()->send();
             return;
        }

        $org = \App\Models\Organization::find($orgId);
        $smsEnabled = app(\App\Services\PlanLimitService::class)->canUseSMS($org);

        /** @var \App\Services\AuditService $auditService */
        $auditService = app(\App\Services\AuditService::class);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 2) continue;
            
            $voterId = trim($data[0]);
            $email = trim($data[1]);
            $phone = isset($data[2]) ? trim($data[2]) : null;
            $department = isset($data[3]) ? trim($data[3]) : null;
            
            if (empty($voterId) || empty($email)) continue;

            // Conditional Validation: If SMS is enabled, Phone is required
            if ($smsEnabled && empty($phone)) {
                $smsRequiredSkipped++;
                continue;
            }

            try {
                $voter = OrganizationUser::where('organization_id', $orgId)
                    ->where('voter_id', $voterId)
                    ->first();

                if ($voter) {
                    // Update existing
                    $oldValues = $voter->getAttributes();
                    
                    $voter->allowed_email = $email;
                    if ($phone) $voter->phone = $phone;
                    if ($department) $voter->department = $department;
                    
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
                        'phone' => $phone,
                        'department' => $department,
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
        
        $message = "Imported {$count} voters successfully";
        if ($errors > 0) $message .= " ({$errors} errors)";
        if ($smsRequiredSkipped > 0) $message .= " ({$smsRequiredSkipped} skipped: missing phone)";
        
        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }
}
