<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentmainsTable extends Migration
{
    public function up()
    {
        Schema::create('paymentmains', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type'); // cash or cheque
            $table->decimal('amount', 10, 2);
            $table->json('denominations')->nullable(); // JSON for cash denominations
            $table->integer('no_of_cheques')->nullable(); // Number of cheques
            $table->timestamps();
        });

        Schema::create('payment_cheques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('paymentmains')->onDelete('cascade');
            $table->string('cheque_number');
            $table->date('cheque_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_cheques');
        Schema::dropIfExists('paymentmain');
    }
};
