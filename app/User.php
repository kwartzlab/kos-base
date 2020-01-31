<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\DB;

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
        'updated_at',
        'date_admitted',
        'date_applied',
        'date_hiatus_start',
        'date_hiatus_end',
        'date_withdrawn'
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

    public function socials() {
        return $this->hasMany(UserSocial::class);
    }

    public function skills() {
        return $this->hasMany(UserSkill::class);
    }

    public function certs() {
        return $this->hasMany(UserCert::class);
    }

    public function team_assignments($team_id = 'all') {
        if ($team_id == 'all') {
            return $this->hasMany(TeamAssignment::class);
        } else {
            return $this->hasMany(TeamAssignment::class)->where('team_id',$team_id);
        }
    }

    // returns user's membership application (if exists)
    public function memberapp() {
        return $this->hasMany(FormSubmission::class,'user_id', 'id')->where('special_form','new_user_app');
    }

    // return user's full name
    public function get_name() {
        return $this->first_name . ' ' . $this->last_name;
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

    // returns true or false if user is a trainer for specified gatekeeper
    public function is_trainer($gatekeeper_id) {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'trainer', 'gatekeeper_id' => $gatekeeper_id]);
        if ($result->count() === 0 ) {
            return false;
        } else {
            return true;
        }

    }

    public function is_maintainer($gatekeeper_id) {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'maintainer', 'gatekeeper_id' => $gatekeeper_id]);
        if ($result->count() === 0 ) {
            return false;
        } else {
            return true;
        }

    }


    // returns training requests (past or present)
    public function training_requests($status = 'all') {
    	if ($status == 'all') {
            return \App\TeamRequest::where(['user_id' => \Auth::user()->id, 'request_type' => 'training'])->orderby('created_at','desc')->get();
        } else if ($status == 'history') {
			return \App\TeamRequest::where('status','!=','new')->where(['user_id' => \Auth::user()->id, 'request_type' => 'training'])->orderby('updated_at','desc')->get();
        } else {
			return \App\TeamRequest::where(['user_id' => \Auth::user()->id, 'request_type' => 'training', 'status' => $status])->orderby('created_at','desc')->get();
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

    // is member a lead of specifed team?
    public function is_team_lead($team_id) {
        $result = \App\TeamAssignment::where(['user_id' => $this->id, 'team_id' => $team_id, 'team_role' => 'lead'])->get();
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // returns all teams the user is part of
    public function teams() {
        return $this->belongsToMany(Team::class, 'team_assignments','user_id');
    }

    public function has_role($role_id) {
        if (count($this->roles()->where('role_id',$role_id)->get())>0) {
            return true;
        } else {
            return false;
        }
    }

    // returns all roles the user holds
    public function roles() {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    // check user roles to verify permissions
    public function is_allowed($object, $operation) {
        return Db::table('role_permissions')
            ->where('object', $object)
            ->where('operation', $operation)
            ->join('user_roles', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->where('user_roles.user_id', $this->id)
            ->exists();
    }

    // can the user program the system?
    public function is_superuser() {
        
        if (count($this->roles()->where('role_id','1')->get())>0) {
            return true;
        } else {
            return false;
        }

    }


}
