<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Rack extends Model
{
    use SoftDeletes;
    protected $fillable = [ 'room_id','label'];
    protected $dates = ['deleted_at'];
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
