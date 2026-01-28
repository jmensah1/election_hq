<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify enum as it is the most reliable way for MySQL
        DB::statement("ALTER TABLE candidates MODIFY COLUMN nomination_status ENUM('pending', 'approved', 'rejected', 'withdrawn', 'pending_submission', 'pending_vetting') NOT NULL DEFAULT 'pending_submission'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original, mapping new statuses back to 'pending' if they exist to avoid data loss on rollback?
        // Risky to change data on down, but we must revert the schema.
        
        // First convert any new statuses to 'pending' to satisfy the old constraint
        DB::table('candidates')
            ->whereIn('nomination_status', ['pending_submission', 'pending_vetting'])
            ->update(['nomination_status' => 'pending']);

        DB::statement("ALTER TABLE candidates MODIFY COLUMN nomination_status ENUM('pending', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending'");
    }
};
