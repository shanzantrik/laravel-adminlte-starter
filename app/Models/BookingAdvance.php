<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BookingAdvance extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'customer_id',
        'order_booking_number',
        'total_amount',
        'sales_exec_name',
        'amount_paid',
        'balance',
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
    /**
     * Prepare the model for JSON serialization.
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['customer'] = $this->customer ? $this->customer->name : 'N/A';
        return $array;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
