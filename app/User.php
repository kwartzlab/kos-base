<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use Notifiable;
    use \OwenIt\Auditing\Auditable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'status', 'acl', 'member_id', 'date_applied','date_admitted','date_hiatus_start','date_hiatus_end','date_withdrawn','phone','address','city','province','postal','photo'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function keys() {

        return $this->hasMany(Key::class);

    }
    public function authorizations() {

        return $this->hasMany(Authorization::class);
        

    }

    // returns all trainer records for current user
    public function trainer_for() {

        $result = \App\Trainers::where('user_id',$this->id)->get();
        if ($result->count() === 0 ) {
            return NULL;
        } else {
            return $result;
        }
    
    }

    // returns true or false if user is authorized for specified gatekeeper
    public function is_authorized($gatekeeper_id) {
        $result = \App\Authorization::where('user_id',$this->id)->where('gatekeeper_id',$gatekeeper_id)->get();
        if ($result->count() === 0 ) {
            return false;
        } else {
            return true;
        }

    }

    public function add_authorization($gatekeeper_id) {

        \App\Authorization::create([
            'user_id' => $this->id,
            'gatekeeper_id' => $gatekeeper_id
            ]);

    }

    // clears specific authorization for a user
    public function delete_authorization($gatekeeper_id) {

        $result = \App\Authorization::where(['user_id',$this->id],['gatekeeper_id',$gatekeeper_id])->delete();
        return true;

    }

    // clears all authorizations for a user
    public function clear_authorizations() {

        $result = \App\Authorization::where('user_id',$this->id)->delete();
        return true;

    }

    // can the user program the system?
    public function is_admin() {
        if ($this->acl == 'admin') { return true; } else { return false; }

    }

    // can the user program keys?
    public function is_keyadmin() {
        if ($this->acl == 'keyadmin') { return true; } else { return false; }

    }





}
