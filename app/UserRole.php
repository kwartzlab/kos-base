<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';
    protected $fillable = ['user_id', 'role_id'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
