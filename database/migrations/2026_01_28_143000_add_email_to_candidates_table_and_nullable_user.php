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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('email')->after('user_id')->nullable(); // Nullable initially for existing records? Or populate?
            // Actually, for new invites it's required. For old ones it might be null if user_id exists.
            
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
