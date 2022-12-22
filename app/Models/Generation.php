<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'generation_type_id',
        'tag_id',
        'prompt',
        'result',
        'command',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function generationType()
    {
        return $this->belongsTo(GenerationType::class,'generation_type_id');
    }
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    public function tag()
    {
        return $this->belongsTo(Tag::class,'tag_id');
    }
    public function getCreatedAtAttribute($date)
    {
        $date = Carbon::parse($date)->timezone('America/Guayaquil');
        return $date->format('Y-m-d H:i:s');
    }
    public function getUpdatedAtAttribute($date)
    {
        $date = Carbon::parse($date)->timezone('America/Guayaquil');
        return $date->format('Y-m-d H:i:s');
    }
}
