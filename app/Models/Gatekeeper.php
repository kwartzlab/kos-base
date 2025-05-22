<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class Gatekeeper extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'last_seen',
    ];

    public function authentications()
    {
        return $this->hasMany(Authentication::class);
    }

    // returns active trainers
    public function trainers($with_status = null)
    {
        if ($with_status == null) {
            return $this->hasMany(TeamAssignment::class)->where(['team_role' => 'trainer']);
        } else {
            return $this->hasMany(TeamAssignment::class)->where(['team_role' => 'trainer', 'status' => $with_status]);
        }
    }

    // returns active maintainers
    public function maintainers($with_status = null)
    {
        if ($with_status == null) {
            return $this->hasMany(TeamAssignment::class)->where(['team_role' => 'maintainer']);
        } else {
            return $this->hasMany(TeamAssignment::class)->where(['team_role' => 'maintainer', 'status' => $with_status]);
        }
    }

    public function current_status()
    {
        return $this->hasOne(GatekeeperStatus::class);
    }

    // determines if current user is a trainer for this gatekeeper
    public function is_trainer()
    {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'trainer', 'user_id' => \Auth::user()->id]);
        if ($result->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // determines if current user is a maintainer for this gatekeeper
    public function is_maintainer()
    {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'maintainer', 'user_id' => \Auth::user()->id]);
        if ($result->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // returns true or false if user is authorized
    public function is_authorized($user_id = 0)
    {
        if ($user_id == 0) {
            $user_id = \Auth::user()->id;
        }
        if ($this->shared_auth > 0) {
            $result = \App\Models\Authorization::where(['user_id' => $user_id, 'gatekeeper_id' => $this->shared_auth])->first();
        } else {
            $result = $this->hasOne(Authorization::class)->where(['user_id' => $user_id]);
        }

        if ($result->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // checks if the supplied key is for an active gatekeeper
    public function authenticate($auth_key)
    {
        if ($auth_key == null) {
            return null;
        }

        $result = \App\Models\Gatekeeper::where('auth_key', $auth_key)->where('status', 'enabled')->get()->first();

        if (count($result) > 0) {
            return $result;
        } else {
            return null;
        }
    }

    // returns the number of authorizations for the gatekeeper
    public function count_authorizations()
    {
        if ($this->shared_auth != 0) {
            $result = \App\Models\Authorization::where('gatekeeper_id', $this->shared_auth)->count();
        } else {
            $result = \App\Models\Authorization::where('gatekeeper_id', $this->id)->count();
        }

        return $result;
    }

    // returns all authorizations for gatekeeper
    public function authorizations()
    {
        if ($this->shared_auth == 0) {
            return $this->hasMany(Authorization::class, 'gatekeeper_id', 'id');
        } else {
            return $this->hasMany(Authorization::class, 'gatekeeper_id', 'id')->where('id', $this->shared_auth);
        }
    }

    // returns team gatekeeper belongs to (if any)
    public function team()
    {
        return $this->hasOne(Team::class, 'id', 'team_id');
    }

    // has user requested training for this gatekeeper?
    public function training_requested()
    {

        // get any non-cancelled records
        $result = \App\Models\TeamRequest::whereNotIn('status', ['cancelled', 'failed'])->where(['gatekeeper_id' => $this->id, 'user_id' => \Auth::user()->id])->count();
        if ($result == 0) {
            return false;
        } else {
            return true;
        }
    }

    // returns requests of a specific type (or all)
    public function training_requests()
    {
        return $this->hasMany(TeamRequest::class, 'gatekeeper_id', 'id');
    }
}
