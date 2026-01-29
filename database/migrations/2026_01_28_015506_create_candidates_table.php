<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email')->nullable(); // For candidate invitations before user claims account
            
            // Candidate info
            $table->string('candidate_number', 20)->nullable();
            $table->text('manifesto')->nullable();
            $table->string('photo_path')->nullable();
            
            // Nomination
            $table->enum('nomination_status', ['pending', 'approved', 'rejected', 'withdrawn', 'pending_submission', 'pending_vetting'])->default('pending_submission');
            $table->timestamp('nominated_at')->useCurrent();
            $table->foreignId('nominated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Vetting
            $table->enum('vetting_status', ['pending', 'passed', 'failed', 'disqualified'])->default('pending');
            $table->text('vetting_notes')->nullable();
            $table->timestamp('vetted_at')->nullable();
            $table->foreignId('vetted_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Results
            $table->integer('vote_count')->default(0);
            $table->boolean('is_winner')->default(false);
            
            $table->timestamps();
            
            $table->unique(['election_id', 'position_id', 'user_id']);
            $table->index('organization_id');
            $table->index('election_id');
            $table->index('position_id');
            $table->index('user_id');
            $table->index('nomination_status');
            $table->index('vetting_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
