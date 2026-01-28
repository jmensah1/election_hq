<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            
            // Notification details
            $table->enum('type', ['email', 'sms']);
            $table->enum('category', ['vote_confirmation', 'election_reminder', 'results', 'admin_alert']);
            
            // Recipient
            $table->string('recipient')->comment('Email address or phone number');
            
            // Content
            $table->string('subject')->nullable();
            $table->text('message');
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            
            // Cost tracking (for SMS)
            $table->decimal('cost_amount', 10, 4)->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('election_id');
            $table->index(['type', 'category']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
