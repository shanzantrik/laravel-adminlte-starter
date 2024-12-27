<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('payment_new_vehicles', function (Blueprint $table) {
      $table->id();
      $table->foreignId('new_vehicle_sale_id')->constrained()->onDelete('cascade');
      $table->string('payment_by');
      $table->date('payment_date');
      $table->decimal('amount', 10, 2);
      $table->string('reference_number')->nullable();
      $table->string('bank_name')->nullable();
      $table->string('approved_by')->nullable();
      $table->string('discount_note_no')->nullable();
      $table->string('approved_note_no')->nullable();
      $table->string('institution_name')->nullable();
      $table->string('credit_instrument')->nullable();
      $table->timestamps();

      // Add index for better performance
      $table->index('payment_by');
      $table->index('payment_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('payment_new_vehicles');
  }
};
