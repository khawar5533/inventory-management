<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'company',
        'email',
        'password',
        'first_name',
        'last_name',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'user_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // make relation ship many to mant
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}
