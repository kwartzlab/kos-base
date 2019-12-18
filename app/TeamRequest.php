<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
 
class TeamRequest extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['request_type', 'status', 'user_id', 'team_id', 'gatekeeper_id'];

    public function team() {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }    

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }    
       
    // returns gatekeeper object of current request
    public function gatekeeper() {
        return $this->belongsTo(Gatekeeper::class, 'gatekeeper_id', 'id');
    }


}
