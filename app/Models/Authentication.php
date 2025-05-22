<?php

namespace App\Models;

class Authentication extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gatekeeper()
    {
        return $this->belongsTo(Gatekeeper::class);
    }
}
