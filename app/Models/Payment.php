<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  protected $fillable = [
    'booking_advance_id',
    'payment_by',
    'payment_date',
    'amount',
    'reference_number',
    'bank_name',
    'approved_by',
    'discount_note_no',
    'approved_note_no',
    'institution_name',
    'credit_instrument',
  ];

  public function bookingAdvance()
  {
    return $this->belongsTo(BookingAdvance::class);
  }
}
