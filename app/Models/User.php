<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class User extends Authenticatable implements AuditableContract
{
    use AuditableTrait;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'first_preferred', 'last_preferred', 'email', 'password', 'status', 'acl', 'member_id', 'phone', 'address', 'city', 'province', 'postal', 'photo', 'notes', 'pronouns',
    ];

    protected $casts = [
        'date_applied' => 'datetime',
        'date_admitted' => 'datetime',
        'date_hiatus_start' => 'datetime',
        'date_hiatus_end' => 'datetime',
        'date_withdrawn' => 'datetime',
    ];

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function keys()
    {
        return $this->hasMany(Key::class);
    }

    public function authentications()
    {
        return $this->hasMany(Authentication::class);
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    public function socials()
    {
        return $this->hasMany(UserSocial::class);
    }

    public function skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function certs()
    {
        return $this->hasMany(UserCert::class);
    }

    public function status_history()
    {
        return $this->hasMany(UserStatus::class)->orderby('created_at');
    }

    public function flags()
    {
        return $this->hasMany(UserFlag::class);
    }

    // returns first instance of status type
    public function first_status($type = null)
    {
        if ($type == null) {
            return $this->hasMany(UserStatus::class)->orderby('created_at')->limit(1);
        } else {
            return $this->hasMany(UserStatus::class)->where('status', $type)->orderby('created_at')->limit(1);
        }
    }

    // returns last instance of status type
    public function last_status($type = null)
    {
        if ($type == null) {
            return $this->hasMany(UserStatus::class)->orderby('created_at', 'desc')->limit(1);
        } else {
            return $this->hasMany(UserStatus::class)->where('status', $type)->orderby('created_at', 'desc')->limit(1);
        }
    }

    public function current_status()
    {
        return $this->hasMany(UserStatus::class)->where('created_at', '<', date('Y-m-d H:i:s', strtotime('tomorrow midnight')))->orderby('created_at', 'desc')->limit(1);
    }

    public function team_assignments($team_id = 'all')
    {
        if ($team_id == 'all') {
            return $this->hasMany(TeamAssignment::class);
        } else {
            return $this->hasMany(TeamAssignment::class)->where('team_id', $team_id);
        }
    }

    // returns user's membership application (if exists)
    public function memberapp()
    {
        return $this->hasMany(FormSubmission::class, 'user_id', 'id')->where('special_form', 'new_user_app');
    }

    // returns all forms the user has submitted (with optional special form filter)
    public function submitted_forms($special_form = null)
    {
        if ($special_form != null) {
            return $this->hasMany(FormSubmission::class, 'submitted_by', 'id')->where('special_form', $special_form)->orderby('created_at');
        } else {
            return $this->hasMany(FormSubmission::class, 'submitted_by', 'id')->orderby('created_at');
        }
    }

    // return user's full name
    public function get_name($piece = null)
    {
        $first_name = $this->first_preferred ?? $this->first_name;
        $last_name = $this->last_preferred ?? $this->last_name;

        switch ($piece) {
            case 'first':
                return $first_name;
                break;
            case 'last':
                return $last_name;
                break;
            default:
                return $first_name.' '.$last_name;
        }
    }

    // returns all trainer records for current user
    public function trainer_for()
    {
        $result = \App\Models\Trainers::where('user_id', $this->id)->get();
        if ($result->count() === 0) {
            return null;
        } else {
            return $result;
        }
    }

    // returns true or false if user is authorized for specified gatekeeper
    public function is_authorized($gatekeeper_id)
    {
        $result = \App\Models\Authorization::where('user_id', $this->id)->where('gatekeeper_id', $gatekeeper_id)->get();
        if ($result->count() === 0) {
            return false;
        } else {
            return true;
        }
    }

    // returns true or false if user is a trainer for specified gatekeeper
    public function is_trainer($gatekeeper_id)
    {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'trainer', 'gatekeeper_id' => $gatekeeper_id]);
        if ($result->count() === 0) {
            return false;
        } else {
            return true;
        }
    }

    public function is_maintainer($gatekeeper_id)
    {
        $result = $this->hasOne(TeamAssignment::class)->where(['team_role' => 'maintainer', 'gatekeeper_id' => $gatekeeper_id]);
        if ($result->count() === 0) {
            return false;
        } else {
            return true;
        }
    }

    // returns training requests (past or present)
    public function training_requests($status = 'all')
    {
        if ($status == 'all') {
            return \App\Models\TeamRequest::where(['user_id' => \Auth::user()->id, 'request_type' => 'training'])->orderby('created_at', 'desc')->get();
        } elseif ($status == 'history') {
            return \App\Models\TeamRequest::where('status', '!=', 'new')->where(['user_id' => \Auth::user()->id, 'request_type' => 'training'])->orderby('updated_at', 'desc')->get();
        } else {
            return \App\Models\TeamRequest::where(['user_id' => \Auth::user()->id, 'request_type' => 'training', 'status' => $status])->orderby('created_at', 'desc')->get();
        }
    }

    public function add_authorization($gatekeeper_id)
    {
        \App\Models\Authorization::create([
            'user_id' => $this->id,
            'gatekeeper_id' => $gatekeeper_id,
        ]);
    }

    // clears specific authorization for a user
    public function delete_authorization($gatekeeper_id)
    {
        $result = \App\Models\Authorization::where(['user_id', $this->id], ['gatekeeper_id', $gatekeeper_id])->delete();

        return true;
    }

    // clears all authorizations for a user
    public function clear_authorizations()
    {
        $result = \App\Models\Authorization::where('user_id', $this->id)->delete();

        return true;
    }

    // can the user program the system?
    public function is_admin()
    {
        if ($this->acl == 'admin') {
            return true;
        } else {
            return false;
        }
    }

    // can the user program keys?
    public function is_keyadmin()
    {
        if ($this->acl == 'keyadmin') {
            return true;
        } else {
            return false;
        }
    }

    // is member a lead of specifed team?
    public function is_team_lead($team_id)
    {
        $result = \App\Models\TeamAssignment::where(['user_id' => $this->id, 'team_id' => $team_id, 'team_role' => 'lead'])->get();
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // returns all teams the user is part of
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_assignments', 'user_id');
    }

    public function has_role($role_id)
    {
        if (count($this->roles()->where('role_id', $role_id)->get()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method should have been a relationship THROUGH `user_roles` to the `roles` table.
     * The $relational parameter has been introduced to switch between the old relationship, and the correct one.
     * TODO: refactor all instances of the old relationship, then remove the switch and only return the belongsToMany
     *
     * @return HasMany|BelongsToMany
     */
    public function roles($relational = false)
    {
        if ($relational) {
            return $this->belongsToMany(Role::class, 'user_roles');
        }

        return $this->hasMany(UserRole::class, 'user_id');
    }

    // check user roles to verify permissions
    public function is_allowed($object, $operation)
    {
        return Db::table('role_permissions')
            ->where('object', $object)
            ->where('operation', $operation)
            ->join('user_roles', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->where('user_roles.user_id', $this->id)
            ->exists();
    }

    // can the user program the system?
    public function is_superuser()
    {
        if (count($this->roles()->where('role_id', '1')->get()) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
