<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'membership_id',
        'next_payment_date',
        'subscription_paypal_id',
        'status',
        'start_date'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
