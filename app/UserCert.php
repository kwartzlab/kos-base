<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserCert extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'type', 'name', 'expiry_date'];

    public function user()
    {
        return $this->hasOne('App\User');
    }
}
