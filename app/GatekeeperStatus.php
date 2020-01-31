<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GatekeeperStatus extends Model
{
    protected $dates = [ 'lock_in', 'last_seen' ];

    public function gatekeeper() {
        return $this->hasOne(Gatekeeper::class);
    }

    public function user() {
        return $this->hasOne(User::class,'id', 'user_id');
    }

}
