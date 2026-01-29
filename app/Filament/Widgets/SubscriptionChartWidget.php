<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\ChartWidget;

class SubscriptionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Subscriptions by Plan';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'half';

    public static function canView(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    protected function getData(): array
    {
        $subscriptions = Organization::query()
            ->selectRaw('subscription_plan, COUNT(*) as count')
            ->groupBy('subscription_plan')
            ->pluck('count', 'subscription_plan')
            ->toArray();

        // Define colors for each plan type
        $planColors = [
            'free' => '#94a3b8',       // slate
            'basic' => '#60a5fa',      // blue
            'professional' => '#a78bfa', // violet
            'enterprise' => '#f59e0b',  // amber
        ];

        $labels = array_map(fn($plan) => ucfirst($plan ?? 'Unknown'), array_keys($subscriptions));
        $colors = array_map(fn($plan) => $planColors[$plan] ?? '#6b7280', array_keys($subscriptions));

        return [
            'datasets' => [
                [
                    'label' => 'Vendors',
                    'data' => array_values($subscriptions),
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
