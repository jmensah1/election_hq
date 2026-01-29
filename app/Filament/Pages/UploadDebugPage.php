<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class UploadDebugPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $title = 'Upload Diagnostics';
    protected static ?string $slug = 'upload-debug';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.upload-debug-page';

    public function getViewData(): array
    {
        return [
            'phpConfig' => $this->getPhpConfig(),
            'storageConfig' => $this->getStorageConfig(),
            'livewireConfig' => $this->getLivewireConfig(),
            'permissionTests' => $this->testPermissions(),
            'symlinkStatus' => $this->checkSymlink(),
        ];
    }

    protected function getPhpConfig(): array
    {
        return [
            'upload_max_filesize' => [
                'value' => ini_get('upload_max_filesize'),
                'bytes' => $this->parseSize(ini_get('upload_max_filesize')),
                'ok' => $this->parseSize(ini_get('upload_max_filesize')) >= 12 * 1024 * 1024,
            ],
            'post_max_size' => [
                'value' => ini_get('post_max_size'),
                'bytes' => $this->parseSize(ini_get('post_max_size')),
                'ok' => $this->parseSize(ini_get('post_max_size')) >= 12 * 1024 * 1024,
            ],
            'memory_limit' => [
                'value' => ini_get('memory_limit'),
                'ok' => true,
            ],
            'file_uploads' => [
                'value' => ini_get('file_uploads') ? 'On' : 'Off',
                'ok' => (bool) ini_get('file_uploads'),
            ],
            'upload_tmp_dir' => [
                'value' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
                'ok' => is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()),
            ],
            'max_file_uploads' => [
                'value' => ini_get('max_file_uploads'),
                'ok' => (int) ini_get('max_file_uploads') >= 10,
            ],
        ];
    }

    protected function getStorageConfig(): array
    {
        $defaultDisk = config('filesystems.default');
        $publicDiskConfig = config('filesystems.disks.public');
        
        return [
            'default_disk' => $defaultDisk,
            'public_disk' => [
                'driver' => $publicDiskConfig['driver'] ?? 'unknown',
                'root' => $publicDiskConfig['root'] ?? 'unknown',
                'url' => $publicDiskConfig['url'] ?? 'unknown',
                'visibility' => $publicDiskConfig['visibility'] ?? 'unknown',
            ],
            'storage_path' => storage_path('app/public'),
            'public_path' => public_path('storage'),
        ];
    }

    protected function getLivewireConfig(): array
    {
        $config = config('livewire.temporary_file_upload', []);
        
        return [
            'disk' => $config['disk'] ?? config('filesystems.default') . ' (default)',
            'directory' => $config['directory'] ?? 'livewire-tmp (default)',
            'rules' => is_array($config['rules'] ?? null) 
                ? implode(', ', $config['rules']) 
                : ($config['rules'] ?? 'required, file, max:12288 (default)'),
            'max_upload_time' => ($config['max_upload_time'] ?? 5) . ' minutes',
        ];
    }

    protected function testPermissions(): array
    {
        $tests = [];
        
        // Test storage/app/public writable
        $publicStoragePath = storage_path('app/public');
        $tests['storage_app_public'] = [
            'path' => $publicStoragePath,
            'exists' => is_dir($publicStoragePath),
            'writable' => is_writable($publicStoragePath),
        ];
        
        // Test candidates/photos directory
        $candidatesPath = storage_path('app/public/candidates/photos');
        $tests['candidates_photos'] = [
            'path' => $candidatesPath,
            'exists' => is_dir($candidatesPath),
            'writable' => is_dir($candidatesPath) && is_writable($candidatesPath),
        ];
        
        // Test livewire-tmp directory
        $livewireTmpPath = storage_path('app/public/livewire-tmp');
        $tests['livewire_tmp'] = [
            'path' => $livewireTmpPath,
            'exists' => is_dir($livewireTmpPath),
            'writable' => is_dir($livewireTmpPath) && is_writable($livewireTmpPath),
        ];
        
        // Actually try to write a test file
        try {
            $testFile = 'debug-test-' . time() . '.txt';
            Storage::disk('public')->put($testFile, 'test');
            Storage::disk('public')->delete($testFile);
            $tests['write_test'] = [
                'result' => 'Success',
                'ok' => true,
            ];
        } catch (\Exception $e) {
            $tests['write_test'] = [
                'result' => 'Failed: ' . $e->getMessage(),
                'ok' => false,
            ];
        }

        return $tests;
    }

    protected function checkSymlink(): array
    {
        $publicStorageLink = public_path('storage');
        $target = storage_path('app/public');
        
        $exists = file_exists($publicStorageLink);
        $isLink = is_link($publicStorageLink);
        $correctTarget = $isLink && readlink($publicStorageLink) === $target;
        
        return [
            'link_path' => $publicStorageLink,
            'target_path' => $target,
            'exists' => $exists,
            'is_symlink' => $isLink,
            'correct_target' => $correctTarget,
            'ok' => $exists && ($isLink || is_dir($publicStorageLink)),
        ];
    }

    protected function parseSize(string $size): int
    {
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        
        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }
}
