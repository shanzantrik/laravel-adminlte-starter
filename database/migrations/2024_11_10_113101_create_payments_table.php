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
            $table->foreignId('booking_advance_id')->constrained()->onDelete('cascade');
            $table->string('payment_by');
            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable(); // For cheque no, card transaction ID, etc.
            $table->string('bank_name')->nullable();
            $table->string('payment_date');
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
