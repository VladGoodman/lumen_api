<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'name', 'email'
    ];

    protected $hidden = [
        'password', 'pivot', 'api_token', 'refresh_token'
    ];

    public function tasks(){
        return $this->hasMany(Task::class, 'executor_user_id', 'id');
    }
    public function userLists(){
        return $this->hasMany(UserLists::class, 'user_id', 'id');
    }

    public function lists(){
        return $this->belongsToMany(Lists::class, 'user_lists', 'user_id', 'list_id');
    }

}
