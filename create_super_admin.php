<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    // Determine email - check if there's already a user to promote or create new
    $email = 'admin@elections-hq.me';
    $password = 'password';

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Super Admin',
            'password' => Hash::make($password),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]
    );

    echo "SUCCESS: Super Admin created/updated.\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "ID: " . $user->id . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
