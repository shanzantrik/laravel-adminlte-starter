<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('payment_service_bills', function (Blueprint $table) {
      $table->id();
      $table->foreignId('service_bill_id')->constrained()->onDelete('cascade');
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
    });
  }

  public function down()
  {
    Schema::dropIfExists('payment_service_bills');
  }
};
