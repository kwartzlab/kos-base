<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public const ROLE_SUPERUSER_NAME = 'Superusers';
    public const ROLE_BOD_NAME = 'BoD';
    public const ROLE_KEY_FOB_ASSIGNER_NAME = 'Key fob assigners';
    public const ROLE_BOOKKEEPER_NAME = 'Bookkeeper';

    protected $table = 'roles';

    protected $fillable = ['name', 'description', 'permissions'];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
