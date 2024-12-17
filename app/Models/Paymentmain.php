<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paymentmain extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_type',
        'amount',
        'denominations',
        'no_of_cheques',
    ];

    protected $casts = [
        'denominations' => 'array',
    ];

    public function cheques()
    {
        return $this->hasMany(PaymentCheque::class);
    }
}
