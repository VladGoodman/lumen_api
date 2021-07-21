<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name', 'list_id', 'executor_user_id', 'is_completed',
        'description', 'urgency'
    ];

    protected $hidden = [
        'pivot', 'updated_at', 'created_at', 'executor_user_id'
    ];

    public function list(){
        return $this->belongsTo(Lists::class);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'user_list');
    }
}
