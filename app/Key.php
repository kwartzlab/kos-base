<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

class Key extends Model implements Auditable
{
    //
    use \OwenIt\Auditing\Auditable;

    public function user() {

        return $this->belongsTo(User::class);

    }

}
