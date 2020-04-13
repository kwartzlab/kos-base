<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    //

    protected $fillable = ['user_id','skill'];

    public function user()
    {
        return $this->hasOne('App\User');
    }

    // return users matching skill name
    public function users() {

        $user_ids = \App\UserSkill::where(['skill' => $this->skill])->pluck('user_id')->toArray();
        if (count($user_ids)>0) {
            $result = \App\User::where('status','active')->orderby('first_preferred')->find($user_ids);
            return $result;
        } else {
            return NULL;
        }

    }


}
