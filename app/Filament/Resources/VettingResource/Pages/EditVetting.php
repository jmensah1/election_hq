<?php

namespace App\Filament\Resources\VettingResource\Pages;

use App\Filament\Resources\VettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditVetting extends EditRecord
{
    protected static string $resource = VettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label('Back to List')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Automatically set vetted_at and vetted_by when vetting status changes
        if (isset($data['vetting_status']) && $data['vetting_status'] !== 'pending') {
            $data['vetted_at'] = now();
            $data['vetted_by'] = auth()->id();

            // Sync nomination_status based on vetting outcome
            $data['nomination_status'] = match ($data['vetting_status']) {
                'passed' => 'approved',
                'failed', 'disqualified' => 'rejected',
                default => $data['nomination_status'] ?? 'pending_vetting',
            };
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Vetting Updated')
            ->body('The candidate vetting information has been saved successfully.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
