<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewVehicleSale extends Model
{
  protected $fillable = [
    'invoice_number',
    'customer_id',
    'vehicle_model',
    'chassis_number',
    'engine_number',
    'color',
    'amount',
    'payment_method',
    'remarks'
  ];

  public function customer(): BelongsTo
  {
    return $this->belongsTo(Customer::class);
  }
}
