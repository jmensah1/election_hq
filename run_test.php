<?php
$output = shell_exec('php artisan test tests/Feature/VoterPortalTest.php 2>&1');
file_put_contents('test_output.txt', $output);
echo "Test run complete.\n";
