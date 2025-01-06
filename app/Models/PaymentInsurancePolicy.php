<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInsurancePolicy extends Model
{
  use HasFactory;

  protected $fillable = [
    'insurance_policy_id',
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

  public function insurancePolicy()
  {
    return $this->belongsTo(InsurancePolicy::class);
  }
}
