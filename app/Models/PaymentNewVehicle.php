<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentNewVehicle extends Model
{
  protected $fillable = [
    'new_vehicle_sale_id',
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

  public function newVehicleSale()
  {
    return $this->belongsTo(NewVehicleSale::class);
  }
}
