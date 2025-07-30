<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
   // returm role id
    public static function getRolesByUserId($userId)
    {
        return DB::table('role_user')
            ->where('user_id', $userId)
            ->pluck('role_id');
    }

}

