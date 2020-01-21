<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
 
class TeamAssignment extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'role_id', 'team_id', 'team_role', 'gatekeeper_id','status'];

    public function team() {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }    

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function gatekeeper() {
        return $this->hasOne(Gatekeeper::class, 'id', 'gatekeeper_id');
    }

}
