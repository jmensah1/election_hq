<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->integer('max_candidates')->default(10);
            $table->integer('max_votes')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('organization_id');
            $table->index('election_id');
            $table->index(['election_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
