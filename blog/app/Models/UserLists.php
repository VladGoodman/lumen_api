<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLists extends Model
{
    protected $fillable = [
        'list_id','user_id'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function list(){
        return $this->belongsTo(Lists::class, 'list_id', 'id');
    }

    public function user(){
//        return $this->morphMany('Lists', 'User');
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



}
