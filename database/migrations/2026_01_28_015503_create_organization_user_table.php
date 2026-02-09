<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // Nullable until first login
            
            // Admin Uploaded Data
            $table->string('voter_id', 50);
            $table->string('allowed_email');
            $table->string('phone')->nullable()->after('allowed_email');
            
            $table->enum('role', ['admin', 'election_officer', 'voter'])->default('voter');
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->boolean('can_vote')->default(true);
            
            $table->string('department', 100)->nullable();
            
            $table->timestamps(); 
            
            $table->unique(['organization_id', 'voter_id']);
            $table->unique(['organization_id', 'allowed_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
