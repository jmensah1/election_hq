<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_no_vote')->default(false);
            
            // ABSOLUTELY NO user_id, NO timestamps
            
            $table->index('organization_id');
            $table->index('election_id');
            $table->index(['election_id', 'position_id']);
            $table->index('candidate_id');
            $table->index(['election_id', 'candidate_id'], 'idx_results');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
