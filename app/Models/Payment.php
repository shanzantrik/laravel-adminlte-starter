<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  use HasFactory;

  protected $fillable = [
    'booking_advance_id',
    'payment_by',
    'amount',
    'reference_number',
    'bank_name',
    'payment_date'
  ];

  public function bookingAdvance()
  {
    return $this->belongsTo(BookingAdvance::class);
  }
}
