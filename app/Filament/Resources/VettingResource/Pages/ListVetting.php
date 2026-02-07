<?php

namespace App\Filament\Resources\VettingResource\Pages;

use App\Filament\Resources\VettingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVetting extends ListRecords
{
    protected static string $resource = VettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Candidates')
                ->badge(fn () => $this->getTableQuery()->count())
                ->badgeColor('primary'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('vetting_status', 'pending'))
                ->badge(fn () => $this->getTableQuery()->where('vetting_status', 'pending')->count())
                ->badgeColor('gray')
                ->icon('heroicon-o-clock'),

            'passed' => Tab::make('Passed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('vetting_status', 'passed'))
                ->badge(fn () => $this->getTableQuery()->where('vetting_status', 'passed')->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle'),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('vetting_status', ['failed', 'disqualified']))
                ->badge(fn () => $this->getTableQuery()->whereIn('vetting_status', ['failed', 'disqualified'])->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VettingResource\Widgets\VettingStatsOverview::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'electionId' => data_get($this->tableFilters, 'election_id.value'),
        ];
    }
}
