<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentJobAdvance extends Model
{
  use HasFactory;

  protected $fillable = [
    'job_advance_id',
    'payment_by',
    'payment_date',
    'amount',
    'reference_number',
    'bank_name',
    'approved_by',
    'discount_note_no',
    'approved_note_no',
    'institution_name',
    'credit_instrument'
  ];

  protected $casts = [
    'payment_date' => 'date',
    'amount' => 'decimal:2'
  ];

  public function jobAdvance()
  {
    return $this->belongsTo(JobAdvance::class);
  }
}
