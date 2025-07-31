<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
      use SoftDeletes;
    // Define fillable fields for mass assignment
    protected $fillable = [
        'name',
        'floor_id',
    ];

    // Define relationship to Floor (optional but recommended)
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
