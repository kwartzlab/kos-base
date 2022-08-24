<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSocial extends Model
{
    //
    protected $fillable = ['service', 'profile'];

    public function user()
    {
        return $this->hasOne(\App\User::class);
    }
}
