<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Changes the settings column from json to jsonb for PostgreSQL compatibility.
     * PostgreSQL cannot use DISTINCT on json columns, only jsonb supports equality operators.
     */
    public function up(): void
    {
        // Use raw SQL for PostgreSQL to change json to jsonb
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE organizations ALTER COLUMN settings TYPE jsonb USING settings::jsonb');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE organizations ALTER COLUMN settings TYPE json USING settings::json');
        }
    }
};
