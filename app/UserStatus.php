<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
 
class UserStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id','status','updated_by', 'note', 'created_at','updated_at'];

    // returns user the request is for
    public function user() {
        return $this->belongsTo(User::class);
    }


}
