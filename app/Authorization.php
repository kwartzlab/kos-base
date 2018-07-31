<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

class Authorization extends Model implements Auditable
{
    //
    use \OwenIt\Auditing\Auditable;


    public function user() {

        return $this->belongsTo(User::class);

    }

    // returns the name of the gatekeeper this authorization is for
    public function name() {
    	$result = \App\Gatekeeper::where('id',$this->gatekeeper_id)->get();
    	return $result->pluck('name');
    }

    // returns the name of the user this authorization record is for
    public function username() {
        $result = \App\User::where('id',$this->user_id)->get();
        
        foreach ($result->pluck('first_name','last_name') as $lastname => $firstname) {
            $full_name = $firstname . ' ' . $lastname;
        }
        
        return $full_name;
    }


}
