<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserFlag extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'flag'];

    public function user()
    {
        return $this->hasOne(\App\Models\User::class);
    }
}
