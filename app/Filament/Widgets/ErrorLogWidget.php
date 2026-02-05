<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\File;

class ErrorLogWidget extends Widget
{
    protected static string $view = 'filament.widgets.error-log-widget';
    
    protected static ?int $sort = 4;
    
    protected int|string|array $columnSpan = 'full';
    
    public $errorCount = 0;
    public $recentLogs = [];

    public function mount()
    {
        $this->refreshLogs();
    }
    
    public function refreshLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            $this->errorCount = 0;
            $this->recentLogs = ['Log file not found.'];
            return;
        }

        // 1. Count Errors (Grepping for error|critical for today)
        // Simple approximation: check last 1000 lines for "ERROR" or "CRITICAL"
        // For accurate daily stats we'd need to parse dates, but for now simple count relative to sample
        
        $content = $this->tailFile($logPath, 50); // Get last 50 lines
        $this->recentLogs = $content;
        
        // Count errors in the snippet
        $params = 0;
        foreach ($content as $line) {
            if (str_contains(strtoupper($line), '.ERROR') || str_contains(strtoupper($line), '.CRITICAL')) {
                $params++;
            }
        }
        $this->errorCount = $params; // This is illustrative "recent errors"
    }

    private function tailFile($filepath, $lines = 10)
    {
        // Adaptive tail function (works on Windows/Linux PHP)
        $handle = fopen($filepath, "r");
        $lineCounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];
        
        while ($lineCounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true; 
                    break; 
                }
                $t = fgetc($handle);
                $pos--;
            }
            $lineCounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $lineCounter - 1] = fgets($handle);
            if ($beginning) break;
        }
        fclose($handle);
        return array_reverse($text);
    }
}
