<x-filament-panels::page>
    <div class="space-y-6">
        {{-- PHP Configuration --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                    PHP Upload Configuration
                </div>
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($phpConfig as $key => $config)
                    <div class="p-4 rounded-lg {{ $config['ok'] ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $key) }}</span>
                            @if($config['ok'])
                                <x-heroicon-o-check-circle class="w-5 h-5 text-success-500" />
                            @else
                                <x-heroicon-o-x-circle class="w-5 h-5 text-danger-500" />
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $config['value'] }}
                            @if(isset($config['bytes']))
                                <span class="text-xs">({{ number_format($config['bytes'] / 1024 / 1024, 1) }} MB)</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4 text-sm text-gray-500">
                <strong>upload_max_filesize</strong> and <strong>post_max_size</strong> should be at least 12MB for candidate photos.
            </div>
        </x-filament::section>

        {{-- Livewire Configuration --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bolt class="w-5 h-5" />
                    Livewire File Upload Configuration
                </div>
            </x-slot>
            
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($livewireConfig as $key => $value)
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- Storage Configuration --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-folder class="w-5 h-5" />
                    Storage Configuration
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium text-gray-500">Default Disk:</span>
                    <span class="ml-2 font-mono text-gray-900 dark:text-gray-100">{{ $storageConfig['default_disk'] }}</span>
                </div>
                
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Public Disk Configuration</h4>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        @foreach($storageConfig['public_disk'] as $key => $value)
                            <dt class="text-gray-500">{{ $key }}:</dt>
                            <dd class="font-mono text-gray-900 dark:text-gray-100 truncate" title="{{ $value }}">{{ $value }}</dd>
                        @endforeach
                    </dl>
                </div>
            </div>
        </x-filament::section>

        {{-- Permission Tests --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-shield-check class="w-5 h-5" />
                    Permission Tests
                </div>
            </x-slot>
            
            <div class="space-y-3">
                @foreach($permissionTests as $key => $test)
                    @if(isset($test['path']))
                        <div class="p-3 rounded-lg {{ ($test['exists'] && $test['writable']) ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm">{{ str_replace('_', ' ', $key) }}</span>
                                <div class="flex items-center gap-2">
                                    @if($test['exists'])
                                        <span class="text-xs bg-success-100 text-success-700 px-2 py-1 rounded">EXISTS</span>
                                    @else
                                        <span class="text-xs bg-danger-100 text-danger-700 px-2 py-1 rounded">MISSING</span>
                                    @endif
                                    @if($test['writable'])
                                        <span class="text-xs bg-success-100 text-success-700 px-2 py-1 rounded">WRITABLE</span>
                                    @else
                                        <span class="text-xs bg-danger-100 text-danger-700 px-2 py-1 rounded">NOT WRITABLE</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 font-mono truncate" title="{{ $test['path'] }}">{{ $test['path'] }}</div>
                        </div>
                    @else
                        <div class="p-3 rounded-lg {{ $test['ok'] ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Write Test</span>
                                @if($test['ok'])
                                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-500" />
                                @else
                                    <x-heroicon-o-x-circle class="w-5 h-5 text-danger-500" />
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $test['result'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-filament::section>

        {{-- Symlink Status --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-link class="w-5 h-5" />
                    Storage Symlink Status
                </div>
            </x-slot>
            
            <div class="p-4 rounded-lg {{ $symlinkStatus['ok'] ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }}">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-medium text-gray-700 dark:text-gray-300">public/storage → storage/app/public</span>
                    @if($symlinkStatus['ok'])
                        <x-heroicon-o-check-circle class="w-6 h-6 text-success-500" />
                    @else
                        <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500" />
                    @endif
                </div>
                
                <dl class="text-sm space-y-1">
                    <div class="flex items-center gap-2">
                        <dt class="text-gray-500 w-32">Exists:</dt>
                        <dd>{{ $symlinkStatus['exists'] ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="text-gray-500 w-32">Is Symlink:</dt>
                        <dd>{{ $symlinkStatus['is_symlink'] ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </div>
            
            @if(!$symlinkStatus['ok'])
                <div class="mt-4 p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                    <p class="text-sm text-danger-600 dark:text-danger-400">
                        Run <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan storage:link</code> to create the symlink.
                    </p>
                </div>
            @endif
        </x-filament::section>

        {{-- Quick Fix Commands --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-command-line class="w-5 h-5" />
                    Quick Fix Commands
                </div>
            </x-slot>
            
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm space-y-2">
                <p># Create storage symlink</p>
                <p class="text-white">php artisan storage:link</p>
                <br>
                <p># Clear config cache</p>
                <p class="text-white">php artisan config:clear</p>
                <br>
                <p># Create candidates/photos directory</p>
                <p class="text-white">mkdir -p storage/app/public/candidates/photos</p>
                <br>
                <p># Create livewire-tmp directory</p>
                <p class="text-white">mkdir -p storage/app/public/livewire-tmp</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
