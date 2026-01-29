<?php

namespace App\Filament\Widgets;

use App\Models\Election;
use App\Models\VoteConfirmation;
use Filament\Widgets\ChartWidget;

class VotingActivityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Voting Activity (Last 30 Days)';
    
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

        // Get vote confirmations for the last 30 days
        // Note: Using VoteConfirmation because Vote table has no timestamps (security measure)
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();
        
        $query = VoteConfirmation::query()
            ->whereBetween('voted_at', [$startDate, $endDate]);
        
        // Filter by organization if not super admin
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }
        
        $votes = $query
            ->selectRaw('DATE(voted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Generate all dates for the period
        $labels = [];
        $data = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $data[] = $votes[$dateKey] ?? 0;
            $current->addDay();
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
