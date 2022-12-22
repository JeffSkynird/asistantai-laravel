<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscription_id',
        'status',
        'invoice_data'
    ];
    public function subscription()
    {
        return $this->belongsTo(Subscription::class,'subscription_id');
    }
}
