<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    //Make relation ship many to many
    public function users()
    {
         return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
    // Make many to many relation ship with pemission table
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

}

