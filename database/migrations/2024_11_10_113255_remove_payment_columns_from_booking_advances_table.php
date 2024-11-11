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
        Schema::table('booking_advances', function (Blueprint $table) {
            $table->dropColumn([
                'cash_amount',
                'cheque_number',
                'bank_name',
                'cheque_amount',
                'neft_ref_no',
                'bank_transfer_amount',
                'card_transaction_id',
                'card_amount',
                'advance_adjustment_ref',
                'adjustment_amount',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_advances', function (Blueprint $table) {
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('cheque_amount', 10, 2)->nullable();
            $table->string('neft_ref_no')->nullable();
            $table->decimal('bank_transfer_amount', 10, 2)->nullable();
            $table->string('card_transaction_id')->nullable();
            $table->decimal('card_amount', 10, 2)->nullable();
            $table->string('advance_adjustment_ref')->nullable();
            $table->decimal('adjustment_amount', 10, 2)->nullable();
        });
    }
};
