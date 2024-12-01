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
    Schema::table('payments', function (Blueprint $table) {
      // Add new columns if they don't exist
      if (!Schema::hasColumn('payments', 'approved_by')) {
        $table->string('approved_by')->nullable();
      }
      if (!Schema::hasColumn('payments', 'discount_note_no')) {
        $table->string('discount_note_no')->nullable();
      }
      if (!Schema::hasColumn('payments', 'approved_note_no')) {
        $table->string('approved_note_no')->nullable();
      }
      if (!Schema::hasColumn('payments', 'institution_name')) {
        $table->string('institution_name')->nullable();
      }
      if (!Schema::hasColumn('payments', 'credit_instrument')) {
        $table->string('credit_instrument')->nullable();
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('payments', function (Blueprint $table) {
      $table->dropColumn([
        'approved_by',
        'discount_note_no',
        'approved_note_no',
        'institution_name',
        'credit_instrument'
      ]);
    });
  }
};
