<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ServiceStatusWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            $this->getDatabaseStatus(),
            $this->getRedisStatus(),
            $this->getNginxStatus(),
        ];
    }

    private function getDatabaseStatus(): Stat
    {
        try {
            DB::select('SELECT 1');
            return Stat::make('Database', 'Operational')
                ->description('MySQL Connection')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success');
        } catch (\Exception $e) {
            return Stat::make('Database', 'Down')
                ->description($e->getMessage())
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger');
        }
    }

    private function getRedisStatus(): Stat
    {
        try {
            Redis::ping();
            return Stat::make('Redis', 'Operational')
                ->description('Cache & Queue')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success');
        } catch (\Exception $e) {
             // If redis is not set up, might return null or error
             // check .env
             if (config('database.redis.default.host')) {
                 return Stat::make('Redis', 'Down')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('danger');
             }
             return Stat::make('Redis', 'N/A')
                ->color('gray');
        }
    }

    private function getNginxStatus(): Stat
    {
        $status = 'Unknown';
        $color = 'gray';

        if (PHP_OS_FAMILY === 'Linux') {
             // Check if nginx is running
             $check = \Illuminate\Support\Facades\Process::run('pgrep nginx');
             if ($check->successful() && !empty($check->output())) {
                 $status = 'Running';
                 $color = 'success';
             } else {
                 $status = 'Stopped';
                 $color = 'danger';
             }
        } else {
            $status = 'Windows (N/A)'; // Laragon uses Nginx/Apache but pgrep wont work
        }

        return Stat::make('Web Server', $status)
            ->description('Nginx Process')
            ->color($color);
    }
}
