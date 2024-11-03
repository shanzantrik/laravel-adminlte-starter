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
            $table->string('order_booking_number');
            $table->string('customer_name');
            $table->string('pan_number')->nullable();
            $table->string('sales_executive_name')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_by')->nullable();  // for payment method
            $table->string('bank_details')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('advance_amount', 10, 2)->nullable();
            $table->string('payment_date')->nullable();
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
