<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('voted_at')->useCurrent();
            
            // Security Audit
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Prevents double-voting at database level
            $table->unique(['election_id', 'position_id', 'user_id'], 'unique_vote_check');
            
            $table->index('organization_id');
            $table->index('election_id');
            $table->index(['user_id', 'election_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_confirmations');
    }
};
