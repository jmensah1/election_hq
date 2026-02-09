<?php
$dirs = [
    'public/storage/candidates',
    'public/storage/organizations',
    'public/storage/imports',
    'public/storage/raw_uploads',
    'public/storage/livewire-tmp'
];

foreach ($dirs as $dir) {
    echo "Processing $dir...\n";
    if (!is_dir($dir)) {
        echo "  Directory not found.\n";
        continue;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        if ($fileinfo->getFilename() === '.gitignore') {
             echo "  Skipping .gitignore\n";
             continue;
        }
        
        if ($fileinfo->isDir()) {
             echo "  Removing directory: " . $fileinfo->getRealPath() . "\n";
            rmdir($fileinfo->getRealPath());
        } else {
             echo "  Removing file: " . $fileinfo->getRealPath() . "\n";
            unlink($fileinfo->getRealPath());
        }
    }
}
echo "Cleanup complete.\n";
