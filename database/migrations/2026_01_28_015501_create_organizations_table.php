<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subdomain')->unique()->nullable();
            $table->string('custom_domain')->unique()->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            
            // Subscription
            $table->enum('subscription_plan', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            
            // Features
            $table->boolean('sms_enabled')->default(false);
            $table->string('sms_sender_id', 11)->nullable();
            $table->integer('max_voters')->default(100);
            
            // Settings
            $table->json('settings')->nullable();
            
            $table->timestamps();
            
            $table->index('slug');
            $table->index('subdomain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
