<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
 
class Team extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name','description'];

    public function assignments() {
        return $this->hasMany(TeamAssignment::class, 'team_id', 'id');
    }    

    public function members() {
        return $this->belongsToMany(User::class, 'team_assignments');
    }

    public function requests() {
        return $this->hasMany(TeamRequest::class, 'team_id', 'id');
    }    

    public function leads() {
        return $this->belongsToMany(User::class, 'team_assignments')->where('team_role','lead');
    }

    public function training_requests($status = 'new') {
        if ($status == 'all') {
            return \App\TeamRequest::where(['request_type' => 'training'])->orderby('created_at','desc')->get();
        } else if ($status == 'history') {
            return \App\TeamRequest::where('status','!=','new')->where(['request_type' => 'training'])->orderby('updated_at','desc')->get();
        } else {
            return \App\TeamRequest::where(['request_type' => 'training', 'status' => $status])->orderby('created_at','desc')->get();
        }
    }

    // returns the gatekeepers team is responsible for
    public function gatekeepers() {
        return $this->hasMany(Gatekeeper::class, 'team_id');
    }

    public function is_member($user_id = 0) {
        if ($user_id == 0) { $user_id = \Auth::user()->id; }
        $results = \App\TeamAssignment::where(['team_id' => $this->id, 'user_id' => $user_id])->get();
        if ($results->count() === 0 ) {
            return false;
        } else {
            return true;
        }
    }

    public function is_trainer($user_id = 0) {
        if ($user_id == 0) { $user_id = \Auth::user()->id; }
        $results = \App\TeamAssignment::where(['team_id' => $this->id, 'user_id' => $user_id, 'team_role' => 'trainer'])->get();
        if ($results->count() === 0 ) {
            return false;
        } else {
            return true;
        }
    }

    public function is_lead($user_id = 0) {
        if ($user_id == 0) { $user_id = \Auth::user()->id; }
        $results = \App\TeamAssignment::where(['team_id' => $this->id, 'user_id' => $user_id, 'team_role' => 'lead'])->get();
        if ($results->count() === 0 ) {
            return false;
        } else {
            return true;
        }
    }

    public function is_maintainer($user_id = 0) {
        if ($user_id == 0) { $user_id = \Auth::user()->id; }
        $results = \App\TeamAssignment::where(['team_id' => $this->id, 'user_id' => $user_id, 'team_role' => 'maintainer'])->get();
        if ($results->count() === 0 ) {
            return false;
        } else {
            return true;
        }
    }

    // returns all team assignment records for a specific role
    public function role_members($team_role) {
        return $this->hasMany(TeamAssignment::class)->where('team_role',$team_role);
    }


}
