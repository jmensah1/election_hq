<?php
$file = 'storage/logs/laravel.log';
if (!file_exists($file)) {
    echo "No log file found.";
    exit;
}
$lines = file($file);
$last = array_slice($lines, -50);
echo implode("", $last);
