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
        Schema::create('booking_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Foreign key to customers table
            $table->string('order_booking_number'); // Unique identifier for the booking
            $table->decimal('total_amount', 10, 2); // Total amount for the booking advance
            $table->enum('payment_by', ['cash', 'cheque', 'bank_transfer', 'card', 'advance_adjustment']);

            // Fields based on payment method
            $table->decimal('cash_amount', 10, 2)->nullable();

            // Fields for cheque payment
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('cheque_amount', 10, 2)->nullable();

            // Fields for bank transfer payment
            $table->string('neft_ref_no')->nullable();
            $table->decimal('bank_transfer_amount', 10, 2)->nullable();

            // Fields for card payment
            $table->string('card_transaction_id')->nullable();
            $table->decimal('card_amount', 10, 2)->nullable();

            // Fields for advance adjustment payment
            $table->string('advance_adjustment_ref')->nullable();
            $table->decimal('adjustment_amount', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_advances');
    }
};
