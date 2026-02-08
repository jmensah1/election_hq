<?php

namespace App\Filament\Widgets;

use App\Models\OrganizationUser;
use Filament\Widgets\ChartWidget;

class MemberRolesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Members by Role';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'half';

    public static function canView(): bool
    {
        // Disabled as per user request
        return false;
    }

    protected function getData(): array
    {
        $orgId = function_exists('current_organization_id') ? current_organization_id() : null;
        
        if (!$orgId) {
            return [
                'datasets' => [['data' => []]],
                'labels' => [],
            ];
        }

        $roles = OrganizationUser::query()
            ->where('organization_id', $orgId)
            ->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Define colors and labels for each role
        $roleConfig = [
            'admin' => ['color' => '#ef4444', 'label' => 'Administrators'],
            'election_officer' => ['color' => '#f59e0b', 'label' => 'Election Officers'],
            'voter' => ['color' => '#22c55e', 'label' => 'Voters'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($roles as $role => $count) {
            $config = $roleConfig[$role] ?? ['color' => '#6b7280', 'label' => ucfirst(str_replace('_', ' ', $role))];
            $labels[] = $config['label'];
            $data[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Members',
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
        return 'doughnut';
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
