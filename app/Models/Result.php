<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = [
        'command',
        'result',
        'status',
        'generation_id',
    ];

    public function generation()
    {
        return $this->belongsTo(Generation::class,'generation_id');
    }
    public function getCreatedAtAttribute($date)
    {
        $date = Carbon::parse($date)->timezone('America/Guayaquil');
        return $date->format('Y-m-d H:i:s');
    }
}
