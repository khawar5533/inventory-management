<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
   protected $fillable = ['name'];
//Many to many relation ship with role tablw
   public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
