<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug');
            
            // Lifecycle dates
            $table->dateTime('nomination_start_date');
            $table->dateTime('nomination_end_date');
            $table->dateTime('vetting_start_date');
            $table->dateTime('vetting_end_date');
            $table->dateTime('voting_start_date');
            $table->dateTime('voting_end_date');
            
            // Status tracking
            $table->enum('status', ['draft', 'nomination', 'vetting', 'voting', 'completed', 'cancelled'])->default('draft');
            
            // Settings
            $table->boolean('require_photo')->default(true);
            $table->integer('max_votes_per_position')->default(1);
            $table->json('voter_eligibility_rules')->nullable();
            
            // Results
            $table->boolean('results_published')->default(false);
            $table->timestamp('results_published_at')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['organization_id', 'slug']);
            $table->index('organization_id');
            $table->index('status');
            $table->index(['voting_start_date', 'voting_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
