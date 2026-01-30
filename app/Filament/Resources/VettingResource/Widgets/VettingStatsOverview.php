<?php

namespace App\Filament\Resources\VettingResource\Widgets;

use App\Models\Candidate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VettingStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $query = Candidate::query()
            ->whereIn('nomination_status', ['pending_vetting', 'approved']);

        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        $total = $query->count();
        $pending = (clone $query)->where('vetting_status', 'pending')->count();
        $passed = (clone $query)->where('vetting_status', 'passed')->count();
        $failed = (clone $query)->whereIn('vetting_status', ['failed', 'disqualified'])->count();

        $avgScore = (clone $query)
            ->whereNotNull('vetting_score')
            ->avg('vetting_score');

        return [
            Stat::make('Total Candidates', $total)
                ->description('Ready for vetting')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Pending Review', $pending)
                ->description('Awaiting vetting')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray')
                ->chart([3, 5, 2, 4, 3, 5, 4]),

            Stat::make('Passed', $passed)
                ->description('Cleared for election')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([2, 3, 5, 4, 6, 5, 7]),

            Stat::make('Failed / Disqualified', $failed)
                ->description('Did not pass vetting')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart([1, 2, 1, 3, 2, 1, 2]),

            Stat::make('Average Score', $avgScore ? round($avgScore, 1) . '/100' : 'N/A')
                ->description('Across all vetted candidates')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
