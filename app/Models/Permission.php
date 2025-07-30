<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Permission extends Model
{
   use SoftDeletes;
   protected $fillable = ['name'];
//Many to many relation ship with role tablw
   public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
