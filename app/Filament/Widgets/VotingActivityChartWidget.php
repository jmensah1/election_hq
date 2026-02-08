<?php

namespace App\Filament\Widgets;

use App\Models\Election;
use App\Models\VoteConfirmation;
use Filament\Widgets\ChartWidget;

class VotingActivityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Voting Activity (Last 24 Hours)';
    
    protected static ?int $sort = 6;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = auth()->user();
        $orgId = null;
        
        // Get organization context for non-super admins
        if (!$user?->is_super_admin) {
            $orgId = function_exists('current_organization_id') ? current_organization_id() : null;
        }

        $startDate = now()->subHours(24);
        $endDate = now();
        
        $query = VoteConfirmation::query()
            ->whereBetween('voted_at', [$startDate, $endDate]);
        
        // Filter by organization if not super admin
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }
        
        // Determine database driver for correct date formatting
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = match ($driver) {
            'pgsql' => 'TO_CHAR(voted_at, \'YYYY-MM-DD HH24:00:00\')',
            'sqlite' => 'strftime(\'%Y-%m-%d %H:00:00\', voted_at)',
            default => 'DATE_FORMAT(voted_at, \'%Y-%m-%d %H:00:00\')', // MySQL/MariaDB
        };
        
        $votes = $query
            ->selectRaw("$dateFormat as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Generate all hours for the period
        $labels = [];
        $data = [];
        $current = $startDate->copy()->startOfHour();
        
        while ($current <= $endDate) {
            $hourKey = $current->format('Y-m-d H:00:00');
            $labels[] = $current->format('H:i');
            $data[] = $votes[$hourKey] ?? 0;
            $current->addHour();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Votes',
                    'data' => $data,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
