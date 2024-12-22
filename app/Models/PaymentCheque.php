<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCheque extends Model
{
    protected $fillable = [
        'paymentmain_id',
        'cheque_number',
        'cheque_date'
    ];

    protected $casts = [
        'cheque_date' => 'date'
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Paymentmain::class, 'paymentmain_id');
    }
}
