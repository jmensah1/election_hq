<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MembersPerVendorChart extends ChartWidget
{
    protected static ?string $heading = 'Top Vendors by Members';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'half';

    public static function canView(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    protected function getData(): array
    {
        $vendors = Organization::query()
            ->select('organizations.name')
            ->selectRaw('COUNT(organization_user.id) as member_count')
            ->leftJoin('organization_user', 'organizations.id', '=', 'organization_user.organization_id')
            ->groupBy('organizations.id', 'organizations.name')
            ->orderByDesc('member_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Members',
                    'data' => $vendors->pluck('member_count')->toArray(),
                    'backgroundColor' => '#6366f1', // indigo
                    'borderColor' => '#4f46e5',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $vendors->pluck('name')->map(fn($name) => strlen($name) > 15 ? substr($name, 0, 15) . '...' : $name)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
