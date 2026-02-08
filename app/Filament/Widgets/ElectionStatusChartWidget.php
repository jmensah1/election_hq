<?php

namespace App\Filament\Widgets;

use App\Models\Election;
use Filament\Widgets\ChartWidget;

class ElectionStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Elections by Status';
    
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'half';

    public static function canView(): bool
    {
        // Disabled as per user request
        return false;
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $query = Election::query();
        
        // Filter by organization for non-super admins
        if (!$user?->is_super_admin) {
            $orgId = function_exists('current_organization_id') ? current_organization_id() : null;
            if ($orgId) {
                $query->where('organization_id', $orgId);
            }
        }

        $statuses = $query
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Define colors and labels for each status
        $statusConfig = [
            'draft' => ['color' => '#94a3b8', 'label' => 'Draft'],
            'nomination' => ['color' => '#f59e0b', 'label' => 'Nomination'],
            'vetting' => ['color' => '#8b5cf6', 'label' => 'Vetting'],
            'voting' => ['color' => '#22c55e', 'label' => 'Voting'],
            'completed' => ['color' => '#3b82f6', 'label' => 'Completed'],
            'cancelled' => ['color' => '#ef4444', 'label' => 'Cancelled'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $status => $count) {
            $config = $statusConfig[$status] ?? ['color' => '#6b7280', 'label' => ucfirst($status)];
            $labels[] = $config['label'];
            $data[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Elections',
                    'data' => $data,
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
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
