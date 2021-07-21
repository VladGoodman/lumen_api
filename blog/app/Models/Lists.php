<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{

    protected $hidden = [
        'pivot', 'user'
    ];

    public function tasks(){
        return $this->hasMany(Task::class, 'list_id', 'id');
    }

    public function userLists(){
        return $this->hasMany(UserLists::class, 'list_id', 'id');
    }

    public function user(){
        return $this->belongsToMany(User::class, 'user_lists', 'list_id', 'user_id');
    }

    public function delete()
    {
        $this->userLists()->first()->delete();
        return  parent::delete();
    }
}
