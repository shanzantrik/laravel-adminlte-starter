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
        'vehicle_registration_no',
    ];
    public function bookingAdvances()
    {
        return $this->hasMany(BookingAdvance::class);
    }
}
