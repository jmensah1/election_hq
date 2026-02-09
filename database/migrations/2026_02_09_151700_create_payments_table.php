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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('paystack_reference')->nullable();
            $table->integer('amount'); // In pesewas
            $table->string('currency')->default('GHS');
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->string('channel')->nullable(); // card, mobile_money, bank, ussd, etc.
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
