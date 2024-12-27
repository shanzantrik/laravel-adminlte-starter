<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class NewVehicleSale extends Model
{
  use HasFactory, Notifiable;
  protected $fillable = [
    'invoice_number',
    'order_booking_number',
    'customer_id',
    'so_name',
    'total_amount',
    'amount_paid',
    'balance'
  ];
  protected $casts = [
    'total_amount' => 'decimal:2',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  public function customer(): BelongsTo
  {
    return $this->belongsTo(Customer::class);
  }
  public function toArray()
  {
    $array = parent::toArray();
    $array['customer'] = $this->customer ? $this->customer->name : 'N/A';
    return $array;
  }

  public function paymentNewVehicles()
  {
    return $this->hasMany(PaymentNewVehicle::class);
  }
}
