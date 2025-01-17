<?php

namespace App\Models;

use App\Models\BookingAdvance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Customer extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'phone_no',
        'pan_number',
    ];
    protected $dates = ['created_at', 'updated_at'];
    public function bookingAdvances()
    {
        return $this->hasMany(BookingAdvance::class);
    }
}
