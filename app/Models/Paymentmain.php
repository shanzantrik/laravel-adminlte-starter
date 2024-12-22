<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paymentmain extends Model
{
    protected $fillable = [
        'payment_type',
        'amount',
        'denominations',
        'no_of_cheques'
    ];

    protected $casts = [
        'denominations' => 'array'
    ];

    public function cheques(): HasMany
    {
        return $this->hasMany(PaymentCheque::class, 'paymentmain_id');
    }
}
