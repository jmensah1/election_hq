<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add yes_no voting support flag to positions
        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('is_yes_no_vote')->default(false)->after('is_active');
        });

        // Make candidate_id nullable in votes to support "No" votes
        Schema::table('votes', function (Blueprint $table) {
            $table->boolean('is_no_vote')->default(false)->after('candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('is_yes_no_vote');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('is_no_vote');
        });
    }
};
