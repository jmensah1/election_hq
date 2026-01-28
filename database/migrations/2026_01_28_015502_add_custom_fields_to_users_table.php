<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->unique()->nullable()->after('email');
            $table->string('avatar')->nullable()->after('google_id');
            $table->boolean('is_super_admin')->default(false)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            
            $table->index('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar', 'is_super_admin', 'last_login_at']);
        });
    }
};
