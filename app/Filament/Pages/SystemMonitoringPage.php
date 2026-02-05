<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SystemMonitoringPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string $view = 'filament.pages.system-monitoring-page';

    protected static ?string $navigationLabel = 'System Monitoring';
    
    protected static ?string $title = 'System Monitoring';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->is_super_admin;
    }
    
    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ServerSpecsWidget::class,
            \App\Filament\Widgets\ServiceStatusWidget::class,
            \App\Filament\Widgets\ApplicationStatsWidget::class,
            \App\Filament\Widgets\ErrorLogWidget::class,
        ];
    }
}
