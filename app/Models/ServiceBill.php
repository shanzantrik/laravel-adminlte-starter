<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ServiceBill extends Model
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'order_booking_number',
    'ro_job_number',
    'vehicle_registration_no',
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

  public function payments()
  {
    return $this->hasMany(PaymentServiceBill::class);
  }
}