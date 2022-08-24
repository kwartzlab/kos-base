<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;

class Authorization extends Model implements Auditable
{
    //
    use \OwenIt\Auditing\Auditable;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gatekeeper()
    {
        return $this->hasOne(Gatekeeper::class, 'id', 'gatekeeper_id');
    }

    // returns the name of the gatekeeper this authorization is for
    public function name()
    {
        $result = \App\Models\Gatekeeper::where('id', $this->gatekeeper_id)->get();

        return $result->pluck('name');
    }
}
