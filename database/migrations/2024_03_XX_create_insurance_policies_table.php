<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('insurance_policies', function (Blueprint $table) {
      $table->id();
      $table->string('proposal_policy_number');
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
    Schema::dropIfExists('insurance_policies');
  }
};
