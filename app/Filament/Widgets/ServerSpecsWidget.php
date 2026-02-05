<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Process;

class ServerSpecsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
    
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            $this->getCpuUsage(),
            $this->getMemoryUsage(),
            $this->getDiskUsage(),
            $this->getUptime(),
        ];
    }

    private function getCpuUsage(): Stat
    {
        $usage = 'N/A';
        $icon = 'heroicon-o-cpu-chip';
        $color = 'gray';

        if (PHP_OS_FAMILY === 'Linux') {
            try {
                // Method 1: loadavg
                $load = sys_getloadavg();
                if ($load) {
                    $usage = $load[0] . ' (1m) / ' . $load[1] . ' (5m)';
                    $color = $load[0] > 4 ? 'danger' : 'success';
                }
                
                // Method 2: more accurate CPU % if possible via top
                // $result = Process::run('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1"%"}\'');
                // Keeping it simple and fast with loadavg for widget to avoid Process overhead on every poll
            } catch (\Exception $e) {
                // ignore
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
             // Basic Windows Check (Very rough)
             // $cmd = "wmic cpu get loadpercentage";
             $usage = 'Windows Dev';
             $color = 'info';
        }

        return Stat::make('CPU Load', $usage)
            ->description('Current System Load')
            ->descriptionIcon($icon)
            ->color($color);
    }

    private function getMemoryUsage(): Stat
    {
        $usage = 'N/A';
        $color = 'gray';

        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $free = Process::run('free -m | grep Mem | awk \'{print $3 "/" $2 " MB"}\'');
                if ($free->successful()) {
                    $usage = trim($free->output());
                    
                    // Parse percent for color
                    $parts = explode('/', $usage);
                    if (count($parts) == 2) {
                        $percent = (intval($parts[0]) / intval($parts[1])) * 100;
                        $color = $percent > 90 ? 'danger' : ($percent > 70 ? 'warning' : 'success');
                    }
                }
            } catch (\Exception $e) {}
        } else {
             $usage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB (App)';
             $color = 'info';
        }

        return Stat::make('Memory Usage', $usage)
            ->description('Used / Total RAM')
            ->descriptionIcon('heroicon-o-server')
            ->color($color);
    }

    private function getDiskUsage(): Stat
    {
        $usage = 'N/A';
        $color = 'gray';
        
        // Disk free space of current directory
        try {
            $path = base_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            
            $usedGb = round($used / 1024 / 1024 / 1024, 2);
            $totalGb = round($total / 1024 / 1024 / 1024, 2);
            $percent = ($used / $total) * 100;
            
            $usage = "{$usedGb} GB / {$totalGb} GB";
            $color = $percent > 90 ? 'danger' : 'success';
        } catch (\Exception $e) {}

        return Stat::make('Disk Usage', $usage)
            ->description('App Partition')
            ->descriptionIcon('heroicon-o-circle-stack')
            ->color($color);
    }

    private function getUptime(): Stat
    {
        $uptime = 'N/A';
        
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                $res = Process::run('uptime -p');
                if ($res->successful()) {
                    $uptime = str_replace('up ', '', trim($res->output()));
                }
            } catch (\Exception $e) {}
        }

        return Stat::make('Server Uptime', $uptime)
            ->icon('heroicon-o-clock');
    }
}
