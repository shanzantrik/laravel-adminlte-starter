<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'cheque_number',
        'cheque_date',
    ];

    public function payment()
    {
        return $this->belongsTo(Paymentmain::class);
    }
}
