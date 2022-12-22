<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenerationType extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'description'
    ];
    public function generations()
    {
        return $this->hasMany(Generation::class);
    }
}
