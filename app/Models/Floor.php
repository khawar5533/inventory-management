<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
   use HasFactory;
   use SoftDeletes;
   protected $fillable = ['location_id', 'name'];

   public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
