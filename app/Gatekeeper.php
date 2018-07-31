<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;
 
class Gatekeeper extends Model implements Auditable
{

	use \OwenIt\Auditing\Auditable;

	public function trainers() {
        return $this->hasMany(Trainers::class);
    }

	// checks if the supplied key is for an active gatekeeper
	public function authenticate($auth_key) {

		if ($auth_key == NULL) { return NULL; }

		$result = \App\Gatekeeper::where('auth_key',$auth_key)->where('status','enabled')->get()->first();

		if (count($result) > 0) {
			return $result;
		} else {
			return NULL;
		}

	}

	// returns the number of authorizations for the gatekeeper
	public function count_authorizations() {

		$result = \App\Authorization::where('gatekeeper_id',$this->id)->count();
				
		return $result;

	}


    # returns if current user is a trainer for specific gatekeeper
    # defaults to current user vs 
    public function is_trainer($user_id = NULL) {
        
        // use current user if parameter left empty
        if ($user_id == NULL) {
            $user_id = \Auth::user()->id;
        }

        $result = \App\Trainers::where('user_id',$user_id)->get();
        if ($result->count() === 0 ) {
            return false;
        } else {
            return true;
        }
    }

}
