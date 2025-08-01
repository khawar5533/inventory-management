<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Box extends Model
{
  
protected $fillable = ['rack_id', 'label'];
use SoftDeletes;
protected $dates = ['deleted_at'];

public function rack()
{
    return $this->belongsTo(Rack::class);
}

}
