<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('service_bills', function (Blueprint $table) {
      $table->id();
      $table->string('order_booking_number');
      $table->string('ro_job_number');
      $table->string('vehicle_registration_no');
      $table->foreignId('customer_id')->constrained()->onDelete('cascade');
      $table->string('so_name');
      $table->decimal('total_amount', 10, 2);
      $table->decimal('amount_paid', 10, 2)->default(0);
      $table->decimal('balance', 10, 2)->default(0);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('service_bills');
  }
};
