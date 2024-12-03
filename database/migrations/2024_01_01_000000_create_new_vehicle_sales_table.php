<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('new_vehicle_sales', function (Blueprint $table) {
      $table->id();
      $table->string('invoice_number')->unique();
      $table->foreignId('customer_id')->constrained()->onDelete('cascade');
      $table->string('vehicle_model');
      $table->string('chassis_number')->unique();
      $table->string('engine_number')->unique();
      $table->string('color');
      $table->decimal('amount', 10, 2);
      $table->string('payment_method');
      $table->string('payment_status')->default('pending');
      $table->text('remarks')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('new_vehicle_sales');
  }
};
