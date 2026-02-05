<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Organization;
use App\Models\User;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class ApplicationStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('Organizations', Organization::count())
                ->description('Total Tenants')
                ->descriptionIcon('heroicon-o-building-office-2'),
                
            Stat::make('Users', User::count())
                ->description('Registered Users')
                ->descriptionIcon('heroicon-o-users'),
                
            Stat::make('Active Elections', Election::where('status', 'voting')->count())
                ->description('Currently Voting')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
                
            Stat::make('Total Votes', DB::table('votes')->count())
                ->description('All Time Votes')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('primary'),
        ];
    }
}
